<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use App\Entity\User;
use App\Repository\UserRepository;

final class LoginController extends AbstractController
{
    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher,
    RateLimiterFactoryInterface $anonymousApiLimiter): JsonResponse
    {
        $limiter = $anonymousApiLimiter->create($request->getClientIp());

        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
        
        $userName = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($userName) || empty($email) || empty($password)) {
            return $this->json(['message' => 'Tous les champs sont requis.'], 400);
        } 
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Adresse e-mail invalide.'], 400);
        }

        try {
            $newUser = new User(); 
            $newUser->setUserName($userName);
            $newUser->setEmail($email);
            $hashPassword = $hasher->hashPassword($newUser, $password);
            $newUser->setPassword($hashPassword);
            $entityManager->persist($newUser);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur s\'est produite lors de la création de votre compte: '.$e->getMessage()], 400);
        }

        return $this->json(['message' => 'Vous êtes enregistré avec succès.'], 201);
    }

    #[Route('/api/login_check', name: 'login_check', methods: ['POST'])]
    public function login_check(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, 
    JWTTokenManagerInterface $JWTManager, RateLimiterFactoryInterface $loginLimiter): JsonResponse
    {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (empty($email) || empty($password)) {
            return $this->json(['message' => 'Tous les champs sont requis.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Adresse e-mail invalide.'], 400);
        }

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Identifiants invalides.'], 400);
        }

        $apiKey = $request->headers->get('apikey');
        $limiter = $loginLimiter->create($apiKey);
        $limiter->reserve(1)->wait();

        $token = $JWTManager->create($user);
        return $this->json(['token' => $token], 200);
    }
}
