<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\Enum\CountryCodeAlpha2;
use App\Enum\Theme;
use App\Repository\UserPreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
#[ApiResource(
    operations: [
      new Get(
        uriTemplate: 'api/me/preferences',
        security: "is_granted('ROLE_USER') && object.getUser() == user",
        normalizationContext: ['groups' => ['pref:read']]
      ),
      new Put(
        uriTemplate: 'api/me/preferences',
        security: "is_granted('ROLE_USER') && object.getUser() == user",
        denormalizationContext: ['groups' => ['pref:write']]
      ),
    ]
)]
class UserPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userPreference', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\Column(nullable:true, enumType: Theme::class)]
    #[Groups(['pref:read', 'pref:write'])]
    private ?Theme $theme = Theme::AUTO;

    #[ORM\Column(nullable: true, enumType: CountryCodeAlpha2::class)]
    #[Groups(['pref:read', 'pref:write'])]
    private ?CountryCodeAlpha2 $language = CountryCodeAlpha2::France;

    #[ORM\Column(nullable: true)]
    #[Groups(['pref:read', 'pref:write'])]
    private ?\DateTimeImmutable $dailyReminder = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['pref:read', 'pref:write'])]
    private ?int $cardsPerSession = 20;

    #[ORM\Column]
    #[Groups(['pref:read', 'pref:write'])]
    private ?bool $notifOnLevelUp = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLanguage(): ?CountryCodeAlpha2
    {
        return $this->language;
    }

    public function setLanguage(?CountryCodeAlpha2 $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getDailyReminder(): ?\DateTimeImmutable
    {
        return $this->dailyReminder;
    }

    public function setDailyReminder(?\DateTimeImmutable $dailyReminder): static
    {
        $this->dailyReminder = $dailyReminder;

        return $this;
    }

    public function getCardsPerSession(): ?int
    {
        return $this->cardsPerSession;
    }

    public function setCardsPerSession(int $cardsPerSession): static
    {
        $this->cardsPerSession = $cardsPerSession;

        return $this;
    }

    public function isNotifOnLevelUp(): ?bool
    {
        return $this->notifOnLevelUp;
    }

    public function setNotifOnLevelUp(bool $notifOnLevelUp): static
    {
        $this->notifOnLevelUp = $notifOnLevelUp;

        return $this;
    }
}
