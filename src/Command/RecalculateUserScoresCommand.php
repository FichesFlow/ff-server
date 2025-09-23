<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recalculate-user-scores',
    description: 'Recalculates the total score for users based on their score events',
)]
class RecalculateUserScoresCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of the user to update (leave empty to update all users)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be updated without making changes')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of users to process in each batch', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $dryRun = $input->getOption('dry-run');
        $batchSize = (int)$input->getOption('batch-size');

        if ($username) {
            $user = $this->userRepository->findOneBy(['username' => $username]);
            if (!$user) {
                $io->error(sprintf('User with username "%s" not found', $username));
                return Command::FAILURE;
            }
            $users = [$user];
            $io->note(sprintf('Recalculating score for user: %s', $user->getUsername()));
        } else {
            $totalUsers = $this->userRepository->count([]);
            $io->note(sprintf('Recalculating scores for %d users', $totalUsers));

            // Process in batches to avoid memory issues
            $users = $this->processBatches($io, $totalUsers, $batchSize, $dryRun);
            return Command::SUCCESS;
        }

        $updatedCount = $this->processUsers($users, $io, $dryRun);

        if (!$dryRun && $updatedCount > 0) {
            $this->entityManager->flush();
        }

        if ($dryRun) {
            $io->success('Dry run completed. No changes were made.');
        } else {
            $io->success(sprintf('Scores recalculated for %d users. %d users were updated.', count($users), $updatedCount));
        }

        return Command::SUCCESS;
    }

    private function processBatches(SymfonyStyle $io, int $totalUsers, int $batchSize, bool $dryRun): int
    {
        $totalBatches = ceil($totalUsers / $batchSize);
        $totalUpdated = 0;

        $io->progressStart($totalBatches);

        for ($i = 0; $i < $totalBatches; $i++) {
            $users = $this->userRepository->findBy([], ['id' => 'ASC'], $batchSize, $i * $batchSize);
            $updatedCount = $this->processUsers($users, $io, $dryRun, false);
            $totalUpdated += $updatedCount;

            if (!$dryRun) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($dryRun) {
            $io->success('Dry run completed. No changes were made.');
        } else {
            $io->success(sprintf('Scores recalculated for %d users. %d users were updated.', $totalUsers, $totalUpdated));
        }

        return $totalUpdated;
    }

    private function processUsers(array $users, SymfonyStyle $io, bool $dryRun, bool $showProgress = true): int
    {
        $updatedCount = 0;

        if ($showProgress) {
            $io->progressStart(count($users));
        }

        foreach ($users as $user) {
            $calculatedScore = $this->calculateUserScore($user);
            $currentScore = $user->getScore();

            if ($calculatedScore !== $currentScore) {
                if ($dryRun) {
                    $io->text(sprintf(
                        '- User "%s" (ID: %s): Would update score from %d to %d',
                        $user->getUsername(),
                        $user->getId(),
                        $currentScore,
                        $calculatedScore
                    ));
                } else {
                    $user->setScore($calculatedScore);
                    $this->entityManager->persist($user);
                    $updatedCount++;
                }
            }

            if ($showProgress) {
                $io->progressAdvance();
            }
        }

        if ($showProgress) {
            $io->progressFinish();
        }

        return $updatedCount;
    }

    private function calculateUserScore(User $user): int
    {
        $total = 0;
        foreach ($user->getScoreEvents() as $event) {
            $total += $event->getValue();
        }
        return $total;
    }
}
