<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\DeckStatsController;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Enum\CardReviewStatus;
use App\Repository\CardRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Index(name: 'card_idx_deck_position', columns: ['deck_id', 'position'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['card:read', 'card:item', 'uuid']]),
        new GetCollection(normalizationContext: ['groups' => ['card:read', 'uuid']]),
    ]
)]
class Card
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['card:read', 'card:item'])]
    private ?Deck $deck = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['card:read', 'card:item', 'deck:item'])]
    private ?int $position = 0;

    /**
     * @var Collection<int, CardSide>
     */
    #[ORM\OneToMany(targetEntity: CardSide::class, mappedBy: 'card', cascade: ['persist'], orphanRemoval: true)]
    #[ApiProperty(writableLink: true)]
    #[Groups(['card:read', 'card:item', 'deck:item'])]
    private Collection $cardSides;

    /**
     * @var Collection<int, ReviewEvent>
     */
    #[ORM\OneToMany(targetEntity: ReviewEvent::class, mappedBy: 'card', orphanRemoval: true)]
    private Collection $reviewEvents;

    /**
     * @var Collection<int, ReviewProgress>
     */
    #[ORM\OneToMany(targetEntity: ReviewProgress::class, mappedBy: 'card', orphanRemoval: true)]
    private Collection $reviewProgress;

    public function __construct()
    {
        $this->cardSides = new ArrayCollection();
        $this->reviewEvents = new ArrayCollection();
        $this->reviewProgress = new ArrayCollection();
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, CardSide>
     */
    public function getCardSides(): Collection
    {
        return $this->cardSides;
    }

    public function addCardSide(CardSide $cardSide): static
    {
        if (!$this->cardSides->contains($cardSide)) {
            $this->cardSides->add($cardSide);
            $cardSide->setCard($this);
        }

        return $this;
    }

    public function removeCardSide(CardSide $cardSide): static
    {
        if ($this->cardSides->removeElement($cardSide)) {
            // set the owning side to null (unless already changed)
            if ($cardSide->getCard() === $this) {
                $cardSide->setCard(null);
            }
        }

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
            $reviewEvent->setCard($this);
        }

        return $this;
    }

    public function removeReviewEvent(ReviewEvent $reviewEvent): static
    {
        if ($this->reviewEvents->removeElement($reviewEvent)) {
            // set the owning side to null (unless already changed)
            if ($reviewEvent->getCard() === $this) {
                $reviewEvent->setCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReviewProgress>
     */
    public function getReviewProgress(): Collection
    {
        return $this->reviewProgress;
    }

    public function addReviewProgress(ReviewProgress $reviewProgress): static
    {
        if (!$this->reviewProgress->contains($reviewProgress)) {
            $this->reviewProgress->add($reviewProgress);
            $reviewProgress->setCard($this);
        }

        return $this;
    }

    public function removeReviewProgress(ReviewProgress $reviewProgress): static
    {
        if ($this->reviewProgress->removeElement($reviewProgress)) {
            // set the owning side to null (unless already changed)
            if ($reviewProgress->getCard() === $this) {
                $reviewProgress->setCard(null);
            }
        }

        return $this;
    }

    #[Groups(['card:stats'])]
    public function getReviewStatus(): string
    {
        if ($this->reviewProgress->isEmpty()) {
            return CardReviewStatus::NEVER_SEEN->value;
        }

        $soonestDueAt = null;
        foreach ($this->reviewProgress as $progress) {
            $dueAt = $progress->getDueAt();
            if ($dueAt === null) {
                continue;
            }
            if ($soonestDueAt === null || $dueAt < $soonestDueAt) {
                $soonestDueAt = $dueAt;
            }
        }

        if ($soonestDueAt === null) {
            return CardReviewStatus::NEVER_SEEN->value;
        }

        $now = new DateTimeImmutable();
        if ($soonestDueAt <= $now) {
            return CardReviewStatus::DUE->value;
        }
        return CardReviewStatus::NOT_DUE_YET->value;
    }
}
