<?php

namespace App\EventSubscriber;

use App\Entity\Deck;
use App\Enum\DeckStatus;
use App\Enum\ScoreEventType;
use App\Service\ScoreLogger;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: 'postUpdate')]
#[AsDoctrineListener(event: 'postPersist')]
final readonly class DeckPublishedSubscriber
{
    public function __construct(
        private ScoreLogger $scoreLogger,
    )
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Deck) {
            return;
        }

        // Check if the deck is being created with PUBLISHED status
        if ($entity->getStatus() === DeckStatus::PUBLISHED->value) {
            $owner = $entity->getOwner();

            if ($owner) {
                $this->scoreLogger->log($owner, ScoreEventType::DECK_PUBLISH, null, false);
            }
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Deck) {
            return;
        }

        // Get the change set from unit of work
        $uow = $args->getObjectManager()->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($entity);

        if (!isset($changeSet['status'])) {
            return;
        }

        [$oldStatus, $newStatus] = $changeSet['status'];

        // Only award points when transitioning to PUBLISHED status
        if ($oldStatus !== DeckStatus::PUBLISHED->value && $newStatus === DeckStatus::PUBLISHED->value) {
            $owner = $entity->getOwner();

            if ($owner) {
                $this->scoreLogger->log($owner, ScoreEventType::DECK_PUBLISH);
            }
        }
    }
}
