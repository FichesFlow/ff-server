<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource]
class Tag
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'parent_tag')]
    private ?self $parent_tag = null;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\ManyToMany(targetEntity: Deck::class, mappedBy: 'tags')]
    private Collection $decks;

    public function __construct()
    {
        $this->parent_tag = new ArrayCollection();
        $this->decks = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function addParentTag(self $parentTag): static
    {
        if (!$this->parent_tag->contains($parentTag)) {
            $this->parent_tag->add($parentTag);
            $parentTag->setParentTag($this);
        }

        return $this;
    }

    public function removeParentTag(self $parentTag): static
    {
        if ($this->parent_tag->removeElement($parentTag)) {
            // set the owning side to null (unless already changed)
            if ($parentTag->getParentTag() === $this) {
                $parentTag->setParentTag(null);
            }
        }

        return $this;
    }

    public function getParentTag(): ?self
    {
        return $this->parent_tag;
    }

    public function setParentTag(?self $parent_tag): static
    {
        $this->parent_tag = $parent_tag;

        return $this;
    }

    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->addTag($this);
        }

        return $this;
    }

    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            $deck->removeTag($this);
        }

        return $this;
    }
}
