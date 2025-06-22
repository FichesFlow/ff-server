<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Enum\CardContentType;
use App\Repository\CardBlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardBlockRepository::class)]
#[ORM\Index(name: 'card_block_idx_content_type', columns: ['content_type'])]
#[ORM\Index(name: 'card_block_idx_content', columns: ['content'])]
#[ApiResource]
class CardBlock
{
    use UuidTrait;

    #[ORM\OneToOne(inversedBy: 'cardBlock', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CardSide $card_side = null;

    #[ORM\Column(enumType: CardContentType::class)]
    private ?CardContentType $content_type = CardContentType::TEXT;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $content = [];

    public function getCardSide(): ?CardSide
    {
        return $this->card_side;
    }

    public function setCardSide(CardSide $card_side): static
    {
        $this->card_side = $card_side;

        return $this;
    }

    public function getContentType(): ?CardContentType
    {
        return $this->content_type;
    }

    public function setContentType(CardContentType $content_type): static
    {
        $this->content_type = $content_type;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }
}
