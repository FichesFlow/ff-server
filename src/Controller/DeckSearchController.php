<?php

namespace App\Controller;

use App\Repository\DeckRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/deck-search')]
class DeckSearchController extends AbstractController
{
    public function __construct(
        private DeckRepository      $deckRepository,
        private SerializerInterface $serializer
    )
    {
    }

    #[Route('', name: 'deck_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $limit = min((int)$request->query->get('limit', 10), 20);
        $searchType = $request->query->get('type', 'title');

        if (empty(trim($query))) {
            return $this->json([
                'results' => [],
                'total' => 0,
                'query' => $query,
                'message' => 'Please provide a search query'
            ]);
        }

        if (strlen(trim($query)) < 2) {
            return $this->json([
                'results' => [],
                'total' => 0,
                'query' => $query,
                'message' => 'Search query must be at least 2 characters long'
            ]);
        }

        try {
            if ($searchType === 'full') {
                $results = $this->deckRepository->searchByTitleAndDescription($query, $limit);
            } else {
                $results = $this->deckRepository->searchByTitle($query, $limit);
            }

            $serializedResults = $this->serializer->serialize(
                $results,
                'json',
                ['groups' => ['deck:read', 'deck:list', 'uuid', 'home:list']]
            );

            return new JsonResponse([
                'results' => json_decode($serializedResults),
                'total' => count($results),
                'query' => $query,
                'searchType' => $searchType,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            return $this->json([
                'error' => 'Search failed',
                'message' => 'An error occurred while searching decks',
                'query' => $query
            ], 500);
        } catch (ExceptionInterface $e) {
            return $this->json([
                'error' => 'Serialization failed',
                'message' => 'An error occurred while processing search results',
                'query' => $query
            ], 500);
        }
    }

    #[Route('/suggestions', name: 'deck_search_suggestions', methods: ['GET'])]
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty(trim($query)) || strlen(trim($query)) < 2) {
            return $this->json([
                'suggestions' => [],
                'query' => $query
            ]);
        }

        try {
            // Get top 5 matches for quick suggestions
            $results = $this->deckRepository->searchByTitle($query, 5);

            $suggestions = array_map(function ($deck) {
                return [
                    'id' => $deck->getId(),
                    'title' => $deck->getTitle(),
                    'cardCount' => $deck->getCardCount(),
                    'rating' => $deck->getRatingAvg()
                ];
            }, $results);

            return $this->json([
                'suggestions' => $suggestions,
                'query' => $query,
                'total' => count($suggestions)
            ]);

        } catch (Exception $e) {
            return $this->json([
                'error' => 'Suggestions failed',
                'suggestions' => [],
                'query' => $query
            ], 500);
        }
    }
}
