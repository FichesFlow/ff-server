<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Index(name: 'card_idx_deck_position', columns: ['deck_id', 'position'])]
#[ApiResource]
class Card
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['deck:item'])]
    private ?int $position = 0;

    /**
     * @var Collection<int, CardSide>
     */
    #[ORM\OneToMany(targetEntity: CardSide::class, mappedBy: 'card', cascade: ['persist'], orphanRemoval: true)]
    #[ApiProperty(writableLink: true)]
    #[Groups(['deck:item'])]
    private Collection $cardSides;

    /**
     * @var Collection<int, ReviewEvent>
     */
    #[ORM\OneToMany(targetEntity: ReviewEvent::class, mappedBy: 'card', orphanRemoval: true)]
    private Collection $reviewEvents;

    public function __construct()
    {
        $this->cardSides = new ArrayCollection();
        $this->reviewEvents = new ArrayCollection();
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
}
