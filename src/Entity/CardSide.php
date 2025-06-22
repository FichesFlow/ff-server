<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Enum\CardSides;
use App\Repository\CardSideRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardSideRepository::class)]
#[ApiResource]
class CardSide
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'cardSides')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    #[ORM\Column(enumType: self::class)]
    private ?CardSides $side = CardSides::FRONT;

    #[ORM\OneToOne(mappedBy: 'card_side', cascade: ['persist', 'remove'])]
    private ?CardBlock $cardBlock = null;

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    public function getSide(): ?CardSides
    {
        return $this->side;
    }

    public function setSide(CardSides $side): static
    {
        $this->side = $side;

        return $this;
    }

    public function getCardBlock(): ?CardBlock
    {
        return $this->cardBlock;
    }

    public function setCardBlock(CardBlock $cardBlock): static
    {
        // set the owning side of the relation if necessary
        if ($cardBlock->getCardSide() !== $this) {
            $cardBlock->setCardSide($this);
        }

        $this->cardBlock = $cardBlock;

        return $this;
    }
}
