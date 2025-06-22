<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Repository\DeckRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckRatingRepository::class)]
#[ORM\UniqueConstraint(name: 'deck_rating_unique', columns: ['deck_id', 'rater_id'])]
#[ApiResource]
class DeckRating
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'deckRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    #[ORM\ManyToOne(inversedBy: 'deckRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $rater = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $rating = 0;

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    public function getRater(): ?User
    {
        return $this->rater;
    }

    public function setRater(?User $rater): static
    {
        $this->rater = $rater;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}
