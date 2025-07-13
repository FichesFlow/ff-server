<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ReviewQueue;
use App\Enum\DeckStatus;
use App\Enum\DeckVisibility;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ReviewQueueSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setOwner', EventPriorities::PRE_VALIDATE],
                ['validateDeckEligibility', EventPriorities::PRE_VALIDATE],
            ],
        ];
    }

    public function setOwner(ViewEvent $event): void
    {
        $reviewQueue = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Only process POST requests for ReviewQueue entities
        if (!$reviewQueue instanceof ReviewQueue || Request::METHOD_POST !== $method) {
            return;
        }

        // Set the current authenticated user as the owner
        $currentUser = $this->security->getUser();
        $reviewQueue->setOwner($currentUser);
    }

    public function validateDeckEligibility(ViewEvent $event): void
    {
        $reviewQueue = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Only process POST requests for ReviewQueue entities
        if (!$reviewQueue instanceof ReviewQueue || Request::METHOD_POST !== $method) {
            return;
        }

        $deck = $reviewQueue->getDeck();
        if (!$deck) {
            throw new AccessDeniedHttpException('No deck specified.');
        }

        $currentUser = $this->security->getUser();

        // Allow if the user is the owner of the deck
        if ($deck->getOwner() === $currentUser) {
            return;
        }

        // Check if deck is published
        if ($deck->getStatus() !== DeckStatus::PUBLISHED) {
            throw new AccessDeniedHttpException('This deck is not published and cannot be added to your review queue.');
        }

        // Check if deck is public
        if ($deck->getVisibility() !== DeckVisibility::PUBLIC) {
            throw new AccessDeniedHttpException('This deck is not public and cannot be added to your review queue.');
        }
    }
}
