<?php

namespace App\Controller;

use App\Service\TextSimiliarityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TextMatchController extends AbstractController
{
    public function __construct(
        private TextSimiliarityService $textSimiliarityService
    ) {}

    #[Route('/api/review/match', name: 'api_review_match', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reviewMatch(Request $request): JsonResponse
    {
        $payload = $request->getPayload();
        $expected = $payload->get('expected');
        $spoken = $payload->get('spoken');

        if (!$expected) {
            return $this->json(['message' => 'Expected text not found'], 422);
        } elseif (!$spoken) {
            return $this->json(['message' => 'Spoken text not found'], 422);
        }

        if (mb_strlen($expected) > 1000) {
            return $this->json(['message' => 'Expected text too long'], 422);
        } elseif (mb_strlen($spoken) > 1000) {
            return $this->json(['message' => 'Spoken text too long'], 422);
        }

        $match = $this->textSimiliarityService->ratio($expected, $spoken);

        return $this->json(['match' => $match]);
    }
}