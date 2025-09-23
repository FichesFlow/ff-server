<?php

namespace App\EventSubscriber;

use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Runs for every entity that is about to be INSERT-ed.
 *
 * The AsDoctrineListener attribute is available since DoctrineBundle 2.7.
 * If you’re on an older version, use the traditional EventSubscriber
 * interface plus service-tag (see below).
 */
#[AsDoctrineListener(event: 'prePersist', priority: 0)]
final readonly class DeckOwnerSubscriber
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Only act on Deck instances
        if (!$entity instanceof Deck) {
            return;
        }

        // If the owner is already set (e.g. fixtures or admin API),
        // leave it alone.
        if (null !== $entity->getOwner()) {
            return;
        }

        // Grab the current user from the security token.
        $user = $this->tokenStorage->getToken()?->getUser();

        // In CLI or anonymously-allowed routes there may be no user;
        // safeguard against it to avoid a TypeError.
        if (!is_object($user)) {
            return;
        }

        $entity->setOwner($user);
    }
}
