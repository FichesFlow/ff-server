<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class CurrentUserController extends AbstractController
{
    #[Route('/api/user-me', name: 'api_users-me', methods: ['GET'])]
    public function getCurrentUser(
        #[CurrentUser] ?User $user,
        SerializerInterface  $serializer
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $context = new ObjectNormalizerContextBuilder()
            ->withGroups(['user:read', 'user:item', 'user:level'])
            ->toArray();

        $userData = $serializer->normalize($user, null, $context);

        return $this->json($userData);
    }
}
