<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Enum\CardSides;
use App\Repository\CardSideRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardSideRepository::class)]
#[ORM\Index(name: 'card_side_idx_card_side', columns: ['card_id', 'side'])]
#[ApiResource]
class CardSide
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'cardSides')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    #[ORM\Column(enumType: self::class)]
    #[Groups(['card:item', 'deck:item'])]
    private ?CardSides $side = CardSides::FRONT;

    #[ORM\OneToOne(mappedBy: 'card_side', cascade: ['persist', 'remove'])]
    #[ApiProperty(writableLink: true)]
    #[Groups(['card:item', 'deck:item'])]
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
