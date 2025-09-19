<?php

namespace App\EventSubscriber;

use App\Entity\Deck;
use App\Entity\DeckRating;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

#[AsDoctrineListener(event: 'postUpdate')]
#[AsDoctrineListener(event: 'postPersist')]
#[AsDoctrineListener(event: 'postRemove')]
readonly class DeckRatingSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof DeckRating) {
            $this->updateDeckRatingStats($entity->getDeck());
        }
    }

    private function updateDeckRatingStats(Deck $deck): void
    {
        $ratings = $this->entityManager->getRepository(DeckRating::class)
            ->findBy(['deck' => $deck]);

        $ratingCount = count($ratings);
        $ratingAvg = 0.00;

        if ($ratingCount > 0) {
            $totalRating = array_sum(array_map(fn($rating) => $rating->getRating(), $ratings));
            $ratingAvg = round($totalRating / $ratingCount, 2);
        }

        $deck->setRatingCount($ratingCount);
        $deck->setRatingAvg($ratingAvg);

        $this->entityManager->persist($deck);
        $this->entityManager->flush();
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof DeckRating) {
            $this->updateDeckRatingStats($entity->getDeck());
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof DeckRating) {
            $this->updateDeckRatingStats($entity->getDeck());
        }
    }
}
