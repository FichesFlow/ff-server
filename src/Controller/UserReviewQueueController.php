<?php

namespace App\Controller;

use App\Entity\ReviewQueue;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class UserReviewQueueController extends AbstractController
{
    #[Route('/api/my-review-queues', name: 'api_my_review_queues', methods: ['GET'])]
    public function getUserReviewQueues(
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $reviewQueues = $entityManager->getRepository(ReviewQueue::class)->findBy(['owner' => $user]);

        $context = new ObjectNormalizerContextBuilder()
            ->withGroups(['queue:item', 'uuid'])
            ->toArray();

        $reviewQueuesData = $serializer->normalize($reviewQueues, null, $context);

        return $this->json($reviewQueuesData);
    }

    #[Route('/api/my-review-queues/{id}', name: 'api_my_review_queue', methods: ['GET'])]
    public function getUserReviewQueue(
        #[CurrentUser] ?User   $user,
        string                 $id,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $reviewQueue = $entityManager->getRepository(ReviewQueue::class)->findOneBy([
            'id' => $id,
            'owner' => $user
        ]);

        if (!$reviewQueue) {
            return $this->json(['message' => 'Review queue not found'], Response::HTTP_NOT_FOUND);
        }

        $context = new ObjectNormalizerContextBuilder()
            ->withGroups(['queue:read', 'queue:item', 'uuid'])
            ->toArray();

        $reviewQueueData = $serializer->normalize($reviewQueue, null, $context);

        return $this->json($reviewQueueData);
    }
}
