<?php

namespace App\EventListener;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;

#[AsEntityListener(event: Events::postPersist, entity: Card::class)]
#[AsEntityListener(event: Events::postRemove, entity: Card::class)]
readonly class DeckCardCountListener
{
    public function __construct(
        private ManagerRegistry $doctrine
    ) {}

    public function postPersist(Card $card, PostPersistEventArgs $event): void
    {
        $this->updateDeckCardCount($card);
    }

    public function postRemove(Card $card, PostRemoveEventArgs $event): void
    {
        $this->updateDeckCardCount($card);
    }

    private function updateDeckCardCount(Card $card): void
    {
        $deck = $card->getDeck();
        if (!$deck) {
            return;
        }

        $entityManager = $this->doctrine->getManager();

        // Count cards directly from the database to ensure accuracy
        $cardCount = $entityManager->getRepository(Card::class)
            ->count(['deck' => $deck]);

        $deck->setCardCount($cardCount);

        // Ensure we're not in a nested transaction by checking if we need to flush
        if ($entityManager->isOpen()) {
            $entityManager->persist($deck);
            $entityManager->flush();
        }
    }
}
