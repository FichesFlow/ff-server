<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\CountryCodeAlpha2;
use App\Enum\Theme;
use App\Repository\UserPreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
#[ApiResource]
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
    private ?Theme $theme = Theme::AUTO;

    #[ORM\Column(nullable: true, enumType: CountryCodeAlpha2::class)]
    private ?CountryCodeAlpha2 $language = CountryCodeAlpha2::France;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dailyReminder = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $cardsPerSession = 20;

    #[ORM\Column]
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

    public function getDailyReminder(): ?\DateTime
    {
        return $this->dailyReminder;
    }

    public function setDailyReminder(?\DateTime $dailyReminder): static
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
