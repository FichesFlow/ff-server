<?php

namespace App\Command;

use App\Entity\ReviewProgress;
use App\Repository\DeckRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'deck:force-cards-due-now',
    description: 'Force all cards of a deck to be due now for all users or a specific user',
)]
class ForceDeckCardsDueNowCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private DeckRepository $deckRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeckRepository         $deckRepository,
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->deckRepository = $deckRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('deckId', InputArgument::REQUIRED, 'The UUID of the deck')
            ->addArgument('userId', InputArgument::OPTIONAL, 'The UUID of the user (optional)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deckId = $input->getArgument('deckId');
        $userId = $input->getArgument('userId');
        $deck = $this->deckRepository->find($deckId);

        if (!$deck) {
            $output->writeln('<error>Deck not found.</error>');
            return Command::FAILURE;
        }

        $now = new DateTime();

        // Get all ReviewProgress for cards in this deck (optionally for a user)
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('rp')
            ->from(ReviewProgress::class, 'rp')
            ->join('rp.card', 'c')
            ->where('c.deck = :deck')
            ->setParameter('deck', $deck);

        if ($userId) {
            $qb->andWhere('rp.user = :userId')
               ->setParameter('userId', $userId);
        }

        $reviewProgresses = $qb->getQuery()->getResult();

        foreach ($reviewProgresses as $progress) {
            $progress->setDueAt(clone $now);
        }

        $this->entityManager->flush();

        $output->writeln(sprintf(
            'Set %d review progress records as due now for deck "%s"%s.',
            count($reviewProgresses),
            $deck->getTitle(),
            $userId ? " and user $userId" : ''
        ));

        return Command::SUCCESS;
    }
}
