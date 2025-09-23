<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\DeckRating;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RateDeckController extends AbstractController
{
    #[Route('/api/decks/{id}/rate', name: 'api_rate_deck', methods: ['POST', 'PUT', 'DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function rateDeck(
        string                 $id,
        Request                $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $deck = $entityManager->getRepository(Deck::class)->find($id);

        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();
        $method = $request->getMethod();

        // Find existing rating
        $deckRating = $entityManager->getRepository(DeckRating::class)
            ->findOneBy(['deck' => $deck, 'rater' => $user]);

        switch ($method) {
            case 'POST':
            case 'PUT':
                $data = json_decode($request->getContent(), true);
                $ratingValue = $data['rating'] ?? null;

                if ($ratingValue === null || $ratingValue < 1 || $ratingValue > 5) {
                    return $this->json(['error' => 'Rating must be between 1 and 5'], 400);
                }

                if (!$deckRating) {
                    $deckRating = new DeckRating();
                    $deckRating->setDeck($deck);
                    $deckRating->setRater($user);
                }

                $deckRating->setRating($ratingValue);
                $entityManager->persist($deckRating);
                $entityManager->flush();

                return $this->json([
                    'message' => 'Rating saved successfully',
                    'rating' => $ratingValue
                ], 201);

            case 'DELETE':
                if (!$deckRating) {
                    return $this->json(['error' => 'No rating found to delete'], 404);
                }

                $entityManager->remove($deckRating);
                $entityManager->flush();

                return $this->json(['message' => 'Rating deleted successfully'], 200);

            default:
                return $this->json(['error' => 'Method not allowed'], 405);
        }
    }

    #[Route('/api/decks/{id}/rate', name: 'api_get_user_deck_rating', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserRating(
        string                 $id,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $deck = $entityManager->getRepository(Deck::class)->find($id);

        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();

        $deckRating = $entityManager->getRepository(DeckRating::class)
            ->findOneBy(['deck' => $deck, 'rater' => $user]);

        if (!$deckRating) {
            return $this->json(['rating' => null], 200);
        }

        return $this->json(['rating' => $deckRating->getRating()], 200);
    }
}
