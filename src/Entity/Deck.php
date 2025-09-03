<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeckStatsController;
use App\Entity\Traits\DateAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Enum\CountryCodeAlpha2;
use App\Enum\DeckStatus;
use App\Enum\DeckVisibility;
use App\Filter\MineDecksFilter;
use App\Repository\DeckRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
#[ORM\Index(name: 'deck_idx_visibility_status', columns: ['visibility', 'status'])]
#[ORM\Index(name: 'deck_idx_rating_avg_count', columns: ['rating_avg', 'rating_count'])]
#[ApiFilter(MineDecksFilter::class, properties: ['mine'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['deck:read', 'deck:item', 'deck:list', 'uuid']]),
        new Get(
            uriTemplate: '/decks/{id}/stats',
            controller: DeckStatsController::class,
            normalizationContext: ['groups' => ['deck:read', 'deck:item', 'uuid']],
            security: "is_fully_authenticated()",
            securityMessage: "Only authenticated users can access deck stats"
        ),
        new GetCollection(
            paginationClientEnabled: true,
            paginationClientItemsPerPage: true,
            normalizationContext: ['groups' => ['deck:read', 'uuid']]
        ),
        new Post(
            normalizationContext: ['groups' => ['deck:read', 'deck:item', 'uuid']],
            security: "is_fully_authenticated()",
            securityMessage: "Only authenticated users can create decks",
        ),
        new Put(
            denormalizationContext: ['groups' => ['deck:update']],
            security: "is_granted('ROLE_USER') and object.getOwner() == user"

        ),
        new Delete(security: "is_granted('ROLE_USER') and object.getOwner() == user")
    ]
)]
class Deck
{
    use UuidTrait;
    use DateAtTrait;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    #[Groups(['deck:item'])]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    #[Groups(['deck:read', 'deck:item', 'deck:list', 'queue:item', 'deck:update'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['deck:read', 'deck:item', 'deck:list', 'queue:item', 'deck:update'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true, enumType: CountryCodeAlpha2::class)]
    #[Groups(['deck:read', 'deck:item', 'deck:update'])]
    private ?CountryCodeAlpha2 $language = null;

    #[ORM\Column(enumType: DeckVisibility::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Groups(['deck:read', 'deck:item', 'deck:list', 'deck:update'])]
    private ?DeckVisibility $visibility = DeckVisibility::UNLISTED;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    #[Groups(['deck:read', 'deck:item'])]
    private float $rating_avg = 0.00;

    #[ORM\Column]
    #[Groups(['deck:read', 'deck:item'])]
    private int $rating_count = 0;

    #[ORM\Column]
    #[Groups(['deck:read', 'deck:item', 'deck:list', 'queue:item'])]
    private int $card_count = 0;

    #[ORM\Column(enumType: DeckStatus::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Groups(['deck:read', 'deck:item', 'deck:list', 'deck:update'])]
    private ?DeckStatus $status = DeckStatus::DRAFT;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'decks')]
    #[Groups(['deck:read', 'deck:item', 'deck:update'])]
    private Collection $tags;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'deck', cascade: ['persist'], orphanRemoval: true)]
    #[ApiProperty(writableLink: true)]
    #[Groups(['deck:read', 'deck:item'])]
    private Collection $cards;

    /**
     * @var Collection<int, DeckRating>
     */
    #[ORM\OneToMany(targetEntity: DeckRating::class, mappedBy: 'deck', orphanRemoval: true)]
    #[Groups(['deck:read', 'deck:item'])]
    private Collection $deckRatings;

    /**
     * @var Collection<int, DeckComment>
     */
    #[ORM\OneToMany(targetEntity: DeckComment::class, mappedBy: 'deck', orphanRemoval: true)]
    #[Groups(['deck:read', 'deck:item'])]
    private Collection $deckComments;

    /**
     * @var Collection<int, ReviewQueue>
     */
    #[ORM\OneToMany(targetEntity: ReviewQueue::class, mappedBy: 'deck', orphanRemoval: true)]
    private Collection $reviewQueues;

    /**
     * @var Collection<int, ReviewSession>
     */
    #[ORM\OneToMany(targetEntity: ReviewSession::class, mappedBy: 'deck')]
    private Collection $reviewSessions;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->cards = new ArrayCollection();
        $this->deckRatings = new ArrayCollection();
        $this->deckComments = new ArrayCollection();
        $this->reviewQueues = new ArrayCollection();
        $this->reviewSessions = new ArrayCollection();
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

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setDeck($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getDeck() === $this) {
                $card->setDeck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeckRating>
     */
    public function getDeckRatings(): Collection
    {
        return $this->deckRatings;
    }

    public function addDeckRating(DeckRating $deckRating): static
    {
        if (!$this->deckRatings->contains($deckRating)) {
            $this->deckRatings->add($deckRating);
            $deckRating->setDeck($this);
        }

        return $this;
    }

    public function removeDeckRating(DeckRating $deckRating): static
    {
        if ($this->deckRatings->removeElement($deckRating)) {
            // set the owning side to null (unless already changed)
            if ($deckRating->getDeck() === $this) {
                $deckRating->setDeck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeckComment>
     */
    public function getDeckComments(): Collection
    {
        return $this->deckComments;
    }

    public function addDeckComment(DeckComment $deckComment): static
    {
        if (!$this->deckComments->contains($deckComment)) {
            $this->deckComments->add($deckComment);
            $deckComment->setDeck($this);
        }

        return $this;
    }

    public function removeDeckComment(DeckComment $deckComment): static
    {
        if ($this->deckComments->removeElement($deckComment)) {
            // set the owning side to null (unless already changed)
            if ($deckComment->getDeck() === $this) {
                $deckComment->setDeck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReviewQueue>
     */
    public function getReviewQueues(): Collection
    {
        return $this->reviewQueues;
    }

    public function addReviewQueue(ReviewQueue $reviewQueue): static
    {
        if (!$this->reviewQueues->contains($reviewQueue)) {
            $this->reviewQueues->add($reviewQueue);
            $reviewQueue->setDeck($this);
        }

        return $this;
    }

    public function removeReviewQueue(ReviewQueue $reviewQueue): static
    {
        if ($this->reviewQueues->removeElement($reviewQueue)) {
            // set the owning side to null (unless already changed)
            if ($reviewQueue->getDeck() === $this) {
                $reviewQueue->setDeck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReviewSession>
     */
    public function getReviewSessions(): Collection
    {
        return $this->reviewSessions;
    }

    public function addReviewSession(ReviewSession $reviewSession): static
    {
        if (!$this->reviewSessions->contains($reviewSession)) {
            $this->reviewSessions->add($reviewSession);
            $reviewSession->setDeck($this);
        }

        return $this;
    }

    public function removeReviewSession(ReviewSession $reviewSession): static
    {
        if ($this->reviewSessions->removeElement($reviewSession)) {
            // set the owning side to null (unless already changed)
            if ($reviewSession->getDeck() === $this) {
                $reviewSession->setDeck(null);
            }
        }

        return $this;
    }
}
