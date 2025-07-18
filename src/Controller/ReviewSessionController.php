<?php

namespace App\Controller;

use App\Entity\ReviewSession;
use App\Entity\User;
use App\Enum\ReviewMode;
use App\Enum\ReviewSessionOrigin;
use App\Enum\ScoreEventType;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\ReviewProgressRepository;
use App\Repository\ReviewSessionRepository;
use App\Service\ScoreLogger;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewSessionController extends AbstractController
{
    #[Route('/api/review_sessions/start', name: 'api_review_sessions_start', methods: ['POST'])]
    public function start(
        #[CurrentUser] ?User     $user,
        Request                  $request,
        EntityManagerInterface   $entityManager,
        DeckRepository           $deckRepository,
        CardRepository           $cardRepository,
        ReviewProgressRepository $reviewProgressRepository,
        ValidatorInterface       $validator,
        SerializerInterface      $serializer
    ): Response
    {
        if ($request->getContent() === '') {
            throw new BadRequestHttpException('Request body cannot be empty');
        }

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        // Validate request
        $constraints = new Assert\Collection([
            'deck' => [new Assert\NotBlank(), new Assert\Uuid()],
            'mode' => [new Assert\NotBlank(), new Assert\Choice(choices: ['flashcard', 'qcm'])],
            'cards' => [
                new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\Count(['min' => 1]),
                    new Assert\All([
                        new Assert\Uuid()
                    ])
                ])
            ],
            'dueLimit' => [
                new Assert\Optional([
                    new Assert\Type('integer'),
                    new Assert\Range(['min' => 0, 'max' => 50])
                ])
            ],
            'newCount' => [
                new Assert\Optional([
                    new Assert\Type('integer'),
                    new Assert\Range(['min' => 0, 'max' => 50])
                ])
            ]
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            throw new BadRequestHttpException((string)$violations);
        }

        if (!$deck = $deckRepository->find($data['deck'])) {
            throw $this->createNotFoundException('Deck not found');
        }

        if (!empty($data['cards'])) {
            // Manual mode: use provided cards
            $origin = ReviewSessionOrigin::MANUAL;
            $cards = $cardRepository->findBy([
                'id' => $data['cards'],
                'deck' => $deck
            ]);

            // Check if all requested cards were found
            if (count($cards) !== count($data['cards'])) {
                throw new BadRequestHttpException('Some requested cards were not found or do not belong to the specified deck');
            }
        } else {
            // Queue mode: auto-select cards
            $origin = ReviewSessionOrigin::QUEUE;
            $dueLimit = $data['dueLimit'] ?? 20;
            $newCount = $data['newCount'] ?? 0;
            $now = new DateTime();

            // Get due cards first (up to dueLimit)
            $dueProgressRecords = $reviewProgressRepository->findDueForUserInDeck($user, $deck, $now, $dueLimit);
            $cards = array_map(fn($progress) => $progress->getCard(), $dueProgressRecords);

            // If newCount is specified, get that many never-seen cards
            if ($newCount > 0) {
                $seenCardIds = array_map(fn($card) => $card->getId(), $cards);
                $neverSeenCards = $cardRepository->findNeverSeenCardsInDeck($user, $deck, $newCount, $seenCardIds);
                $cards = array_merge($cards, $neverSeenCards);
            }

            if (empty($cards)) {
                return $this->json(['message' => 'No cards available for review in this deck'], Response::HTTP_OK);
            }
        }

        // Create a review session
        $reviewSession = new ReviewSession();
        $reviewSession->setReviewer($user);
        $reviewSession->setDeck($deck);
        $reviewSession->setMode(ReviewMode::from($data['mode']));
        $reviewSession->setOrigin($origin);

        $entityManager->persist($reviewSession);
        $entityManager->flush();

        // Normalize card objects with appropriate serialization groups
        $context = new ObjectNormalizerContextBuilder()
            ->withGroups(['card:read', 'card:item', 'uuid'])
            ->toArray();

        $normalizedCards = $serializer->normalize($cards, null, $context);

        $response = [
            'id' => $reviewSession->getId(),
            'cards' => $normalizedCards,
            'origin' => $origin
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route('/api/review_sessions/{id}/finish', name: 'api_review_sessions_finish', methods: ['POST'])]
    public function finish(
        string                  $id,
        #[CurrentUser] ?User    $user,
        EntityManagerInterface  $entityManager,
        ReviewSessionRepository $reviewSessionRepository,
        ScoreLogger             $scoreLogger,
        SerializerInterface     $serializer
    ): Response
    {
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $reviewSession = $reviewSessionRepository->find($id);
        if (!$reviewSession) {
            return $this->json(['message' => 'Review session not found'], Response::HTTP_NOT_FOUND);
        }

        if ($reviewSession->getReviewer() !== $user) {
            return $this->json(['message' => 'You can only finish your own review sessions'], Response::HTTP_FORBIDDEN);
        }

        if ($reviewSession->getFinishedAt() !== null) {
            return $this->json(['message' => 'This review session is already finished'], Response::HTTP_BAD_REQUEST);
        }

        $reviewSession->setFinishedAt(new DateTimeImmutable());

        // Calculate final success percentage
        $totalScore = 0;
        $cardsSeen = $reviewSession->getCardsSeen();
        $goodCards = 0;

        foreach ($reviewSession->getReviewEvents() as $event) {
            $score = $event->getScore();
            $totalScore += $score;

            // Count cards with score >= 3 for XP calculation
            if ($score >= 3) {
                $goodCards++;
            }
        }

        // Calculate percentage (max score would be 5 points per card)
        $successPct = $cardsSeen > 0 ? ($totalScore / ($cardsSeen * 5)) * 100 : 0;
        $reviewSession->setSuccessPct($successPct);

        // Calculate XP gained (2 XP per card with score >= 3)
        $xpGained = $goodCards * 2;
        $reviewSession->setXpGained($xpGained);

        if ($xpGained > 0) {
            $scoreLogger->log($user, ScoreEventType::COMPLETE_STUDY_SESSION, $xpGained);
        }

        $entityManager->persist($reviewSession);
        $entityManager->flush();

        $context = new ObjectNormalizerContextBuilder()
            ->withGroups(['review_session:read', 'review_session:item'])
            ->toArray();

        $reviewSessionData = $serializer->normalize($reviewSession, null, $context);

        return $this->json($reviewSessionData, Response::HTTP_OK);
    }
}
