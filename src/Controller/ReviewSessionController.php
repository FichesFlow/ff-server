<?php

namespace App\Controller;

use App\Entity\ReviewSession;
use App\Entity\User;
use App\Enum\ReviewMode;
use App\Enum\ScoreEventType;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\ReviewSessionRepository;
use App\Service\ScoreLogger;
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
        #[CurrentUser] ?User   $user,
        Request                $request,
        EntityManagerInterface $entityManager,
        DeckRepository         $deckRepository,
        CardRepository         $cardRepository,
        ValidatorInterface     $validator
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
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Uuid()
                ])
            ],
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            throw new BadRequestHttpException((string)$violations);
        }

        if (!$deck = $deckRepository->find($data['deck'])) {
            throw $this->createNotFoundException('Deck not found');
        }

        // Get cards by provided IDs, ensuring they belong to the specified deck
        $cards = $cardRepository->findBy([
            'id' => $data['cards'],
            'deck' => $deck
        ]);

        // Check if all requested cards were found
        if (count($cards) !== count($data['cards'])) {
            throw new BadRequestHttpException('Some requested cards were not found or do not belong to the specified deck');
        }

        // Create a review session
        $reviewSession = new ReviewSession();
        $reviewSession->setReviewer($user);
        $reviewSession->setDeck($deck);
        $reviewSession->setMode(ReviewMode::from($data['mode']));

        $entityManager->persist($reviewSession);
        $entityManager->flush();

        $response = [
            'id' => $reviewSession->getId()
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
