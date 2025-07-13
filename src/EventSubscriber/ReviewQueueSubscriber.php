<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ReviewQueue;
use App\Enum\DeckStatus;
use App\Enum\DeckVisibility;
use App\Enum\ReviewPriority;
use App\Repository\ReviewQueueRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ReviewQueueSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security              $security,
        private ReviewQueueRepository $reviewQueueRepository
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setOwner', EventPriorities::PRE_VALIDATE],
                ['validateDeckEligibility', EventPriorities::PRE_VALIDATE],
                ['handleReviewQueue', EventPriorities::PRE_WRITE],
            ],
        ];
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

        if ($deck->getStatus() !== DeckStatus::PUBLISHED) {
            throw new AccessDeniedHttpException('This deck is not published and cannot be added to your review queue.');
        }

        if ($deck->getVisibility() !== DeckVisibility::PUBLIC) {
            throw new AccessDeniedHttpException('This deck is not public and cannot be added to your review queue.');
        }
    }

    public function handleReviewQueue(ViewEvent $event): void
    {
        $reviewQueue = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$reviewQueue instanceof ReviewQueue) {
            return;
        }

        if (Request::METHOD_POST === $method) {
            $currentUser = $this->security->getUser();
            $reviewQueue->setOwner($currentUser);

            if (null === $reviewQueue->getPriority()) {
                $reviewQueue->setPriority(ReviewPriority::NORMAL);
            }

            $deck = $reviewQueue->getDeck();
            if (!$deck) {
                throw new BadRequestHttpException('No deck specified.');
            }

            // Check if the deck is already in the user's review queue
            $existingQueue = $this->reviewQueueRepository->findOneBy([
                'owner' => $currentUser,
                'deck' => $deck
            ]);

            if ($existingQueue) {
                throw new BadRequestHttpException('This deck is already in your review queue.');
            }

            // Allow if the user is the owner of the deck
            if ($deck->getOwner() !== $currentUser) {
                if ($deck->getStatus() !== DeckStatus::PUBLISHED) {
                    throw new AccessDeniedHttpException('This deck is not published and cannot be added to your review queue.');
                }

                if ($deck->getVisibility() !== DeckVisibility::PUBLIC) {
                    throw new AccessDeniedHttpException('This deck is not public and cannot be added to your review queue.');
                }
            }
        } else if (Request::METHOD_PUT === $method) {
            $request = $event->getRequest();
            $data = json_decode($request->getContent(), true);

            // Prevent deck modification
            if (isset($data['deck'])) {
                throw new AccessDeniedHttpException('Deck cannot be modified after creation.');
            }
        }
    }

    public function setOwner(ViewEvent $event): void
    {
        $reviewQueue = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$reviewQueue instanceof ReviewQueue || Request::METHOD_POST !== $method) {
            return;
        }

        $currentUser = $this->security->getUser();
        $reviewQueue->setOwner($currentUser);
    }
}
