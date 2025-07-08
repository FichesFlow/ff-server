<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $hasher
    ): JsonResponse
    {
        $payload = $request->getPayload();
        $userName = $payload->get('userName');
        $email = $payload->get('email');
        $password = $payload->get('password');

        if (empty($userName) || empty($email) || empty($password)) {
            return $this->json(['message' => 'Tous les champs sont requis.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Adresse e-mail invalide.'], 400);
        }

        try {
            $newUser = new User();
            $newUser->setUserName($userName)
                ->setEmail($email)
                ->setPassword($hasher->hashPassword($newUser, $password))
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTime());

            $entityManager->persist($newUser);
            $entityManager->flush();
        } catch (Exception $e) {
            return $this->json(['message' => 'Une erreur s\'est produite lors de la création de votre compte: ' . $e->getMessage()], 400);
        }

        return $this->json(['message' => 'Vous êtes enregistré avec succès.'], 201);
    }

    #[Route('/api/login_check', name: 'login_check', methods: ['POST'])]
    public function login_check(
        Request                     $request,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface    $JWTManager
    ): JsonResponse
    {
        $payload = $request->getPayload();
        $email = $payload->get('email');
        $password = $payload->get('password');

        if (empty($email) || empty($password)) {
            return $this->json(['message' => 'Tous les champs sont requis.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Adresse e-mail invalide.'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Identifiants invalides.'], 400);
        }

        return $this->json(['token' => $JWTManager->create($user)], 200);
    }
}
