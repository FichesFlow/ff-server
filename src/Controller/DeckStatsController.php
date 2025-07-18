<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\User;
use App\Service\ReviewStats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class DeckStatsController extends AbstractController
{
    public function __construct(
        private readonly ReviewStats $reviewStats,
        private readonly SerializerInterface $serializer
    ) {}

    public function __invoke(Deck $data): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User must be authenticated');
        }

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Invalid user type');
        }

        // Serialize deck with full item context
        $deckData = $this->serializer->normalize($data, 'json', [
            'groups' => ['deck:read', 'deck:item', 'card:stats', 'uuid']
        ]);

        // Add review statistics
        $reviewStats = $this->reviewStats->getDeckReviewStats($data, $user);
        $deckData['review'] = $reviewStats;

        return new JsonResponse($deckData);
    }
}

