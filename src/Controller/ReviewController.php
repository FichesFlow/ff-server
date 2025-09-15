<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DeckRepository;
use App\Repository\ReviewProgressRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/review')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewProgressRepository $reviewProgressRepository,
        private readonly DeckRepository           $deckRepository
    )
    {
    }

    #[Route('/due', name: 'api_review_due', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueCards(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $now = new DateTime();
        $deckCounts = $this->reviewProgressRepository->countDueForUserGroupedByDeck($user, $now);

        $total = array_sum(array_column($deckCounts, 'count'));

        $decks = array_map(fn($deckData) => [
            'id' => $deckData['deck_id'],
            'title' => $deckData['deck_name'],
            'due' => $deckData['count']
        ], $deckCounts);

        return $this->json([
            'total' => $total,
            'decks' => $decks
        ]);
    }

    #[Route('/due/count', name: 'api_review_due_count', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueCount(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $now = new DateTime();
        $dueCount = $this->reviewProgressRepository->countDueForUser($user, $now);

        return $this->json([
            'total' => $dueCount
        ]);
    }

    #[Route('/due/by-deck', name: 'api_review_due_by_deck', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueCardsByDeck(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $now = new DateTime();
        $deckCounts = $this->reviewProgressRepository->countDueForUserGroupedByDeck($user, $now);

        return $this->json([
            'decks' => $deckCounts
        ]);
    }

    #[Route('/due/deck/{deckId}', name: 'api_review_due_deck', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueCardsForDeck(string $deckId, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $deck = $this->deckRepository->find($deckId);
        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        $limit = (int)$request->query->get('limit', 20);
        $limit = max(1, min(100, $limit)); // Clamp between 1 and 100

        $now = new DateTime();
        $dueProgressRecords = $this->reviewProgressRepository->findDueForUserInDeck($user, $deck, $now, $limit);

        // Get the Card objects
        $cards = array_map(fn($progress) => $progress->getCard(), $dueProgressRecords);
        $normalizedCards = $serializer->normalize($cards, null, ['groups' => ['card:item']]);

        return $this->json([
            'cards' => $normalizedCards,
        ]);
    }

    #[Route('/due/session', name: 'api_review_due_session', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueSession(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $now = new DateTime();
        $dueProgressRecords = $this->reviewProgressRepository->findDueForUser($user, $now);

        // Get the Card objects
        $cards = array_map(fn($progress) => $progress->getCard(), $dueProgressRecords);
        $normalizedCards = $serializer->normalize($cards, null, ['groups' => ['card:item']]);

        return $this->json([
            'cards' => $normalizedCards,
            'total' => count($cards)
        ]);
    }
}
