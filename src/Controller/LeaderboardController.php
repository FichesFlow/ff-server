<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class LeaderboardController extends AbstractController
{
    #[Route('/api/leaderboard', name: 'api_leaderboard', methods: ['GET'])]
    #[Cache(public: true, maxage: 30)]
    public function __invoke(Request $request, UserRepository $userRepository): JsonResponse
    {
        $period = $request->query->get('period', 'week');
        $page = (int) $request->query->get('page', 1);
        $size = (int) $request->query->get('size', 20);

        if (!in_array($period, ['all', 'day', 'week', 'month', 'year'])) {
            return $this->json(['message' => 'Period must be either day, week, month, year or all'], 422);
        } elseif ($page < 1) {
            return $this->json(['message' => 'Page must be equal or greater than 1'], 422);
        } elseif ($size < 10 || $size > 100) {
            return $this->json(['message' => 'Size must be between 10 and 100'], 422);
        }

        [$entries, $total] = $userRepository->getLeaderboard($period, $page, $size);

        return $this->json([
            'page'    => $page,
            'size'    => $size,
            'total'   => $total,
            'entries' => $entries
        ], 200);
    }
}