<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\ScoreEventType;
use App\Repository\ScoreEventRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ScoreEventRepository::class)]
#[ORM\Index(name: "score_event_created_at_idx", columns: ["created_at"])]
#[ApiResource]
class ScoreEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'scoreEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $scorer = null;

    #[ORM\Column(enumType: ScoreEventType::class)]
    private ?ScoreEventType $type = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScorer(): ?User
    {
        return $this->scorer;
    }

    public function setScorer(?User $scorer): static
    {
        $this->scorer = $scorer;

        return $this;
    }

    public function getType(): ?ScoreEventType
    {
        return $this->type;
    }

    public function setType(ScoreEventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
