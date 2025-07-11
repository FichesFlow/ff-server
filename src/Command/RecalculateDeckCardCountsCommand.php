<?php

namespace App\Command;

use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recalculate-deck-card-counts',
    description: 'Recalculates the number of cards in decks',
)]
class RecalculateDeckCardCountsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeckRepository         $deckRepository,
        private readonly CardRepository         $cardRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('deck-id', InputArgument::OPTIONAL, 'UUID of the deck to update (leave empty to update all decks)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be updated without making changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $deckId = $input->getArgument('deck-id');
        $dryRun = $input->getOption('dry-run');

        if ($deckId) {
            $deck = $this->deckRepository->find($deckId);
            if (!$deck) {
                $io->error(sprintf('Deck with ID "%s" not found', $deckId));
                return Command::FAILURE;
            }
            $decks = [$deck];
            $io->note(sprintf('Recalculating card count for deck: %s', $deck->getTitle()));
        } else {
            $decks = $this->deckRepository->findAll();
            $io->note(sprintf('Recalculating card counts for %d decks', count($decks)));
        }

        $updatedCount = 0;
        $io->progressStart(count($decks));

        foreach ($decks as $deck) {
            $actualCount = $this->cardRepository->count(['deck' => $deck]);
            $currentCount = $deck->getCardCount();

            if ($actualCount !== $currentCount) {
                if ($dryRun) {
                    $io->text(sprintf(
                        '- Deck "%s" (ID: %s): Would update count from %d to %d',
                        $deck->getTitle(),
                        $deck->getId(),
                        $currentCount,
                        $actualCount
                    ));
                } else {
                    $deck->setCardCount($actualCount);
                    $this->entityManager->persist($deck);
                    $updatedCount++;
                }
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        if (!$dryRun && $updatedCount > 0) {
            $this->entityManager->flush();
        }

        if ($dryRun) {
            $io->success('Dry run completed. No changes were made.');
        } else {
            $io->success(sprintf('Card counts recalculated for %d decks. %d decks were updated.', count($decks), $updatedCount));
        }

        return Command::SUCCESS;
    }
}
