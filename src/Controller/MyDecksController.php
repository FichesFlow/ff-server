<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DeckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MyDecksController extends AbstractController
{
    #[Route('/api/my-decks', name: 'api_my_decks', methods: ['GET'])]
    public function __invoke(Request $request, DeckRepository $deckRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User must be authenticated');
        }

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Invalid user type');
        }

        // Get pagination parameters
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 500)));
        $offset = ($page - 1) * $limit;

        // Get optional status filter
        $status = $request->query->get('status');
        $visibility = $request->query->get('visibility');

        // Build base query
        $queryBuilder = $deckRepository->createQueryBuilder('d')
            ->where('d.owner = :user')
            ->setParameter('user', $user);

        // Add status filter if provided
        if ($status) {
            $queryBuilder->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }

        // Add visibility filter if provided
        if ($visibility) {
            $queryBuilder->andWhere('d.visibility = :visibility')
                ->setParameter('visibility', $visibility);
        }

        // Get total count for pagination (without ORDER BY)
        $countQueryBuilder = clone $queryBuilder;
        $totalCount = $countQueryBuilder
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Get the actual results (with ORDER BY)
        $decks = $queryBuilder
            ->orderBy('d.updated_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->json($decks, 200, [], ['groups' => ['deck:read', 'deck:list', 'uuid']]);
    }
}
