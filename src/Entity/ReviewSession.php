<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\UuidTrait;
use App\Enum\ReviewMode;
use App\Repository\ReviewSessionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReviewSessionRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['review_session:read', 'review_session:item', 'uuid']]),
        new GetCollection(normalizationContext: ['groups' => ['review_session:read', 'uuid']]),
    ]
)]
class ReviewSession
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'reviewSessions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review_session:item'])]
    private ?User $reviewer = null;

    #[ORM\ManyToOne(inversedBy: 'reviewSessions')]
    #[Groups(['review_session:read'])]
    private ?Deck $deck = null;

    #[ORM\Column(enumType: ReviewMode::class)]
    #[Groups(['review_session:item'])]
    private ?ReviewMode $mode = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['review_session:item'])]
    private ?DateTimeImmutable $started_at = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    #[Groups(['review_session:item'])]
    private ?DateTimeImmutable $finished_at = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['review_session:item'])]
    private ?int $cards_seen = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '0.00'])]
    #[Groups(['review_session:item'])]
    private ?float $success_pct = 0.00;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['review_session:item'])]
    private ?int $xp_gained = 0;

    /**
     * @var Collection<int, ReviewEvent>
     */
    #[ORM\OneToMany(targetEntity: ReviewEvent::class, mappedBy: 'session', orphanRemoval: true)]
    private Collection $reviewEvents;

    public function __construct()
    {
        $this->reviewEvents = new ArrayCollection();
    }

    public function getReviewer(): ?User
    {
        return $this->reviewer;
    }

    public function setReviewer(?User $reviewer): static
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    public function getMode(): ?ReviewMode
    {
        return $this->mode;
    }

    public function setMode(ReviewMode $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->started_at;
    }

    public function setStartedAt(DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function getCardsSeen(): ?int
    {
        return $this->cards_seen;
    }

    public function setCardsSeen(int $cards_seen): static
    {
        $this->cards_seen = $cards_seen;

        return $this;
    }

    public function getSuccessPct(): ?float
    {
        return $this->success_pct;
    }

    public function setSuccessPct(float $success_pct): static
    {
        $this->success_pct = $success_pct;

        return $this;
    }

    public function getXpGained(): ?int
    {
        return $this->xp_gained;
    }

    public function setXpGained(int $xp_gained): static
    {
        $this->xp_gained = $xp_gained;

        return $this;
    }

    /**
     * @return Collection<int, ReviewEvent>
     */
    public function getReviewEvents(): Collection
    {
        return $this->reviewEvents;
    }

    public function addReviewEvent(ReviewEvent $reviewEvent): static
    {
        if (!$this->reviewEvents->contains($reviewEvent)) {
            $this->reviewEvents->add($reviewEvent);
            $reviewEvent->setSession($this);
        }

        return $this;
    }

    public function removeReviewEvent(ReviewEvent $reviewEvent): static
    {
        if ($this->reviewEvents->removeElement($reviewEvent)) {
            // set the owning side to null (unless already changed)
            if ($reviewEvent->getSession() === $this) {
                $reviewEvent->setSession(null);
            }
        }

        return $this;
    }
}
