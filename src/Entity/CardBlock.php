<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Repository\CardBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardBlockRepository::class)]
#[ORM\Index(name: 'card_block_idx_content', columns: ['content'])]
#[ApiResource]
class CardBlock
{
    use UuidTrait;

    #[ORM\OneToOne(inversedBy: 'cardBlock', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CardSide $card_side = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['card:item', 'card:write', 'card:read', 'deck:item'])]
    private ?string $content;

    public function getCardSide(): ?CardSide
    {
        return $this->card_side;
    }

    public function setCardSide(CardSide $card_side): static
    {
        $this->card_side = $card_side;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
