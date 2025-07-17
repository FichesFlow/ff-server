<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ReviewEvent;
use App\Entity\User;
use App\Repository\ReviewSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ReviewEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security                $security,
        private ReviewSessionRepository $sessionRepository,
        private EntityManagerInterface  $entityManager
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['processReviewEvent', EventPriorities::PRE_WRITE],
                ['updateSessionStatistics', EventPriorities::POST_WRITE]
            ]
        ];
    }

    public function processReviewEvent(ViewEvent $event): void
    {
        $reviewEvent = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$reviewEvent instanceof ReviewEvent || Request::METHOD_POST !== $method) {
            return;
        }

        // Get the current user and set as reviewer
        if (!$currentUser = $this->security->getUser()) {
            throw new LogicException('The user must be logged in to create a review event.');
        }

        if (!$currentUser instanceof User) {
            throw new LogicException('The current user must be an instance of User.');
        }

        $reviewEvent->setReviewer($currentUser);

        $data = json_decode($event->getRequest()->getContent(), true);
        if (!isset($data['session'])) {
            throw new LogicException('The session ID must be provided.');
        }

        // Extract the UUID from the IRI
        $sessionIri = $data['session'];
        $sessionId = substr($sessionIri, strrpos($sessionIri, '/') + 1);

        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            throw new LogicException('The specified session does not exist.');
        }

        // Verify if the card belongs to the deck of the session
        $card = $reviewEvent->getCard();
        $sessionDeck = $session->getDeck();

        if ($sessionDeck && $card && !$sessionDeck->getCards()->contains($card)) {
            throw new LogicException('The card does not belong to the deck of this session.');
        }
    }

    public function updateSessionStatistics(ViewEvent $event): void
    {
        $reviewEvent = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$reviewEvent instanceof ReviewEvent || Request::METHOD_POST !== $method) {
            return;
        }

        $session = $reviewEvent->getSession();
        if (!$session) {
            return;
        }

        // Update session statistics
        $cardsSeen = $session->getCardsSeen() + 1;
        $session->setCardsSeen($cardsSeen);

        // Calculate new success percentage
        $totalScore = 0;
        foreach ($session->getReviewEvents() as $event) {
            $totalScore += $event->getScore();
        }

        // Calculate percentage (max score would be 5 points per card)
        $successPct = ($totalScore / ($cardsSeen * 5)) * 100;
        $session->setSuccessPct($successPct);

        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }
}
