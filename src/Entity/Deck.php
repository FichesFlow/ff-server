<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Enum\CountryCodeAlpha2;
use App\Enum\DeckStatus;
use App\Enum\DeckVisibility;
use App\Repository\DeckRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
#[ApiResource]
class Deck
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, enumType: CountryCodeAlpha2::class)]
    private ?CountryCodeAlpha2 $language = null;

    #[ORM\Column(enumType: DeckVisibility::class)]
    private ?DeckVisibility $visibility = DeckVisibility::UNLISTED;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private float $rating_avg = 0.00;

    #[ORM\Column]
    private int $rating_count = 0;

    #[ORM\Column]
    private int $card_count = 0;

    #[ORM\Column(enumType: DeckStatus::class)]
    private ?DeckStatus $status = DeckStatus::DRAFT;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'decks')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLanguage(): ?CountryCodeAlpha2
    {
        return $this->language;
    }

    public function setLanguage(?CountryCodeAlpha2 $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getVisibility(): ?DeckVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(DeckVisibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getRatingAvg(): ?float
    {
        return $this->rating_avg;
    }

    public function setRatingAvg(float $rating_avg): static
    {
        $this->rating_avg = $rating_avg;

        return $this;
    }

    public function getRatingCount(): ?int
    {
        return $this->rating_count;
    }

    public function setRatingCount(int $rating_count): static
    {
        $this->rating_count = $rating_count;

        return $this;
    }

    public function getCardCount(): ?int
    {
        return $this->card_count;
    }

    public function setCardCount(int $card_count): static
    {
        $this->card_count = $card_count;

        return $this;
    }

    public function getStatus(): ?DeckStatus
    {
        return $this->status;
    }

    public function setStatus(DeckStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
