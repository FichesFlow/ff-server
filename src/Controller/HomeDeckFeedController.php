<?php

namespace App\Controller;

use App\Repository\DeckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class HomeDeckFeedController extends AbstractController
{
    #[Route('/api/home/decks', name:'api_home_decks', methods: ['GET'])]
    #[Cache(public: true, maxage: 60)]
    public function __invoke(Request $request, DeckRepository $deckRepository): JsonResponse
    {
        $payload = $request->getPayload();
        $size = $payload->get('size', 12);

        if ($size < 1 || $size > 24) {
            return $this->json(['message' => 'Size must be between 1 and 24'], 422);
        }

        $top = $payload->get('top', 6);

        if ($top < 0 || $top > $size) {
            return $this->json(['message' => 'Top must be between 0 and '.$size], 422);
        }

        $best = $deckRepository->findTopRated($top);
        $ids = array_column($best, 'id');
        $random = $deckRepository->findRandomPublicPublished($size - count($best), $ids);

        $mix = array_merge($best, $random);
        shuffle($mix);

        return $this->json(['decks' => $mix], 200, [], ['groups' => ['home:list']]);
    }
}