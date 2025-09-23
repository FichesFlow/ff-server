<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Traits\UuidTrait;
use App\Enum\ReviewPriority;
use App\Repository\ReviewQueueRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReviewQueueRepository::class)]
#[ORM\UniqueConstraint(name: 'owner_deck_unique', columns: ['owner_id', 'deck_id'])]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['queue:read', 'queue:item', 'uuid']],
            security: "object.getOwner() == user"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['queue:read', 'uuid']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            normalizationContext: ['groups' => ['queue:read', 'queue:item', 'uuid']],
            denormalizationContext: ['groups' => ['queue:write']],
            security: "is_granted('ROLE_USER')",
        ),
        new Put(
            normalizationContext: ['groups' => ['queue:read', 'queue:item', 'uuid']],
            denormalizationContext: ['groups' => ['queue:update']],
            security: "object.getOwner() == user"
        ),
        new Delete(
            security: "object.getOwner() == user"
        ),
    ]
)]
class ReviewQueue
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'reviewQueues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['queue:read'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'reviewQueues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['queue:read', 'queue:write', 'queue:item'])]
    private ?Deck $deck = null;

    #[ORM\Column(enumType: ReviewPriority::class, options: ["default" => ReviewPriority::NORMAL])]
    #[Groups(['queue:read', 'queue:write', 'queue:update', 'queue:item'])]
    private ?ReviewPriority $priority = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['queue:read', 'queue:item'])]
    private ?DateTimeImmutable $added_at = null;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
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

    public function getPriority(): ?ReviewPriority
    {
        return $this->priority;
    }

    public function setPriority(ReviewPriority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAddedAt(): ?DateTimeImmutable
    {
        return $this->added_at;
    }

    public function setAddedAt(DateTimeImmutable $added_at): static
    {
        $this->added_at = $added_at;

        return $this;
    }
}
