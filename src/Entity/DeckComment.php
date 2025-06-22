<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\DeckCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckCommentRepository::class)]
#[ApiResource]
class DeckComment
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\ManyToOne(inversedBy: 'deckComments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    #[ORM\ManyToOne(inversedBy: 'deckComments')]
    private ?User $commenter = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    public function getCommenter(): ?User
    {
        return $this->commenter;
    }

    public function setCommenter(?User $commenter): static
    {
        $this->commenter = $commenter;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }
}
