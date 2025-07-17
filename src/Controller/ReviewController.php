<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReviewProgressRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/review')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewProgressRepository $reviewProgressRepository
    ) {
    }

    #[Route('/due', name: 'api_review_due', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDueCards(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $limit = (int) $request->query->get('limit', 20);
        $limit = max(1, min(100, $limit)); // Clamp between 1 and 100

        $now = new DateTime();
        $dueProgressRecords = $this->reviewProgressRepository->findDueForUser($user, $now, $limit);

        $cardIds = array_map(fn($progress) => $progress->getCard()->getId(), $dueProgressRecords);
        $totalCount = $this->reviewProgressRepository->countDueForUser($user, $now);

        return $this->json([
            'cards' => $cardIds,
            'count' => $totalCount
        ]);
    }
}

