<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPreference;
use App\Enum\CountryCodeAlpha2;
use App\Enum\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserPreferenceController extends AbstractController
{
    #[Route('/api/me/preferences', name: 'api_me_preferences', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getPreferences(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $preferences = $user->getUserPreference();

        if (!$preferences) {
            return $this->json(['message' => 'Preferences not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($preferences, Response::HTTP_OK, [], ['groups' => ['pref:read']]);
    }

    #[Route('/api/me/preferences', name: 'api_me_preferences_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updatePreferences(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $preferences = $user->getUserPreference();
        $payload = $request->getPayload();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$preferences) {
            $preferences = new UserPreference();
            $preferences->setOwner($user);
        }

        $theme = $payload->get('theme');
        $language = $payload->get('language');
        $cardsPerSession = $payload->get('cardsPerSession');

        if ($theme && !in_array($theme, Theme::cases())) {
            return $this->json(['message' => 'Invalid theme'], 422);
        } elseif ($language && !in_array($language, CountryCodeAlpha2::cases())) {
            return $this->json(['message' => 'Invalid language'], 422);
        } elseif ($cardsPerSession && ($cardsPerSession < 5 || $cardsPerSession > 100)) {
            return $this->json(['message' => 'Cards per session must be between 5 and 100'], 422);
        }

        if ($theme) {
            $preferences->setTheme(Theme::from($theme));
        }

        if ($language) {
            $preferences->setLanguage(CountryCodeAlpha2::from($language));
        }

        if ($cardsPerSession) {
            $preferences->setCardsPerSession($cardsPerSession);
        }

        $entityManager->persist($preferences);
        $entityManager->flush();

        return $this->json(['message' => 'Preferences updated successfully'], 200);
    }
}