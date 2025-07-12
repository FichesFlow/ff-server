<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['user:read', 'user:item', 'user:level']]),
        new GetCollection(normalizationContext: ['groups' => ['user:read']])
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidTrait;
    use DateAtTrait;

    private const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 1000,
        6 => 2000,
        7 => 3500,
        8 => 5000,
        9 => 7500,
        10 => 10000
    ];

    #[ORM\Column(length: 180)]
    #[Groups(['deck:item'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:item'])]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $avatar_url = null;

    /**
     * @var Collection<int, UserBadge>
     */
    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $userBadges;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'reporter')]
    private Collection $reports;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'resolver')]
    private Collection $reports_resolved;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\OneToMany(targetEntity: Deck::class, mappedBy: 'owner')]
    private Collection $decks;

    /**
     * @var Collection<int, DeckRating>
     */
    #[ORM\OneToMany(targetEntity: DeckRating::class, mappedBy: 'rater', orphanRemoval: true)]
    private Collection $deckRatings;

    /**
     * @var Collection<int, DeckComment>
     */
    #[ORM\OneToMany(targetEntity: DeckComment::class, mappedBy: 'commenter')]
    private Collection $deckComments;

    /**
     * @var Collection<int, ScoreEvent>
     */
    #[ORM\OneToMany(targetEntity: ScoreEvent::class, mappedBy: 'scorer', orphanRemoval: true)]
    private Collection $scoreEvents;

    #[ORM\Column(options: ["default" => 0])]
    private ?int $score = 0;

    public function __construct()
    {
        $this->userBadges = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->reports_resolved = new ArrayCollection();
        $this->decks = new ArrayCollection();
        $this->deckRatings = new ArrayCollection();
        $this->deckComments = new ArrayCollection();
        $this->scoreEvents = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function setAvatarUrl(?string $avatar_url): static
    {
        $this->avatar_url = $avatar_url;

        return $this;
    }

    /**
     * @return Collection<int, UserBadge>
     */
    public function getUserBadges(): Collection
    {
        return $this->userBadges;
    }

    public function addUserBadge(UserBadge $userBadge): static
    {
        if (!$this->userBadges->contains($userBadge)) {
            $this->userBadges->add($userBadge);
            $userBadge->setOwner($this);
        }

        return $this;
    }

    public function removeUserBadge(UserBadge $userBadge): static
    {
        if ($this->userBadges->removeElement($userBadge)) {
            // set the owning side to null (unless already changed)
            if ($userBadge->getOwner() === $this) {
                $userBadge->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setReporter($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getReporter() === $this) {
                $report->setReporter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsResolved(): Collection
    {
        return $this->reports_resolved;
    }

    public function addReportsResolved(Report $reportsResolved): static
    {
        if (!$this->reports_resolved->contains($reportsResolved)) {
            $this->reports_resolved->add($reportsResolved);
            $reportsResolved->setResolver($this);
        }

        return $this;
    }

    public function removeReportsResolved(Report $reportsResolved): static
    {
        if ($this->reports_resolved->removeElement($reportsResolved)) {
            // set the owning side to null (unless already changed)
            if ($reportsResolved->getResolver() === $this) {
                $reportsResolved->setResolver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->setOwner($this);
        }

        return $this;
    }

    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            // set the owning side to null (unless already changed)
            if ($deck->getOwner() === $this) {
                $deck->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeckRating>
     */
    public function getDeckRatings(): Collection
    {
        return $this->deckRatings;
    }

    public function addDeckRating(DeckRating $deckRating): static
    {
        if (!$this->deckRatings->contains($deckRating)) {
            $this->deckRatings->add($deckRating);
            $deckRating->setRater($this);
        }

        return $this;
    }

    public function removeDeckRating(DeckRating $deckRating): static
    {
        if ($this->deckRatings->removeElement($deckRating)) {
            // set the owning side to null (unless already changed)
            if ($deckRating->getRater() === $this) {
                $deckRating->setRater(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeckComment>
     */
    public function getDeckComments(): Collection
    {
        return $this->deckComments;
    }

    public function addDeckComment(DeckComment $deckComment): static
    {
        if (!$this->deckComments->contains($deckComment)) {
            $this->deckComments->add($deckComment);
            $deckComment->setCommenter($this);
        }

        return $this;
    }

    public function removeDeckComment(DeckComment $deckComment): static
    {
        if ($this->deckComments->removeElement($deckComment)) {
            // set the owning side to null (unless already changed)
            if ($deckComment->getCommenter() === $this) {
                $deckComment->setCommenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScoreEvent>
     */
    public function getScoreEvents(): Collection
    {
        return $this->scoreEvents;
    }

    public function addScoreEvent(ScoreEvent $scoreEvent): static
    {
        if (!$this->scoreEvents->contains($scoreEvent)) {
            $this->scoreEvents->add($scoreEvent);
            $scoreEvent->setScorer($this);
        }

        return $this;
    }

    public function removeScoreEvent(ScoreEvent $scoreEvent): static
    {
        if ($this->scoreEvents->removeElement($scoreEvent)) {
            // set the owning side to null (unless already changed)
            if ($scoreEvent->getScorer() === $this) {
                $scoreEvent->setScorer(null);
            }
        }

        return $this;
    }

    public function recalculateScore(): static
    {
        $total = 0;
        foreach ($this->scoreEvents as $event) {
            $total += $event->getValue();
        }
        $this->score = $total;

        return $this;
    }

    public function addToScore(int $points): static
    {
        $this->score += $points;

        return $this;
    }

    /**
     * Get the points needed to reach the next level
     */
    #[Groups(['user:level'])]
    #[ApiProperty(description: 'Points needed to reach the next level (null if at max level)')]
    public function getPointsToNextLevel(): ?int
    {
        $currentLevel = $this->getLevel();
        $currentScore = $this->getScore();

        // If at max level, return null
        if ($currentLevel >= max(array_keys(self::LEVEL_THRESHOLDS))) {
            return null;
        }

        $nextLevel = $currentLevel + 1;
        $nextLevelThreshold = self::LEVEL_THRESHOLDS[$nextLevel];

        return $nextLevelThreshold - $currentScore;
    }

    /**
     * Get the user's current level based on their score
     */
    #[Groups(['user:level'])]
    #[ApiProperty(description: "The user's current level based on their score")]
    public function getLevel(): int
    {
        $score = $this->getScore();
        $level = 1;

        foreach (self::LEVEL_THRESHOLDS as $lvl => $threshold) {
            if ($score >= $threshold) {
                $level = $lvl;
            } else {
                break;
            }
        }

        return $level;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get the progress percentage to the next level (0-100)
     */
    #[Groups(['user:level'])]
    #[ApiProperty(description: 'Progress percentage toward the next level (0-100)')]
    public function getLevelProgress(): ?float
    {
        $currentLevel = $this->getLevel();
        $currentScore = $this->getScore();

        // If at max level, return 100%
        if ($currentLevel >= max(array_keys(self::LEVEL_THRESHOLDS))) {
            return 100.0;
        }

        $nextLevel = $currentLevel + 1;
        $currentLevelThreshold = self::LEVEL_THRESHOLDS[$currentLevel];
        $nextLevelThreshold = self::LEVEL_THRESHOLDS[$nextLevel];

        $levelRange = $nextLevelThreshold - $currentLevelThreshold;
        $scoreInLevel = $currentScore - $currentLevelThreshold;

        return min(100.0, round(($scoreInLevel / $levelRange) * 100, 1));
    }

}
