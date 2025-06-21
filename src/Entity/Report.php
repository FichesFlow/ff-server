<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\DateAtTrait;
use App\Enum\ReportStatus;
use App\Enum\ReportTargetType;
use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ApiResource]
class Report
{
    use DateAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?User $reporter = null;

    #[ORM\Column(enumType: ReportTargetType::class)]
    private ?ReportTargetType $target_type = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $target_id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reason = null;

    #[ORM\Column(enumType: ReportStatus::class)]
    private ?ReportStatus $status = null;

    #[ORM\ManyToOne(inversedBy: 'reports_resolved')]
    private ?User $resolver = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getTargetType(): ?ReportTargetType
    {
        return $this->target_type;
    }

    public function setTargetType(ReportTargetType $target_type): static
    {
        $this->target_type = $target_type;

        return $this;
    }

    public function getTargetId(): ?Uuid
    {
        return $this->target_id;
    }

    public function setTargetId(?Uuid $target_id): static
    {
        $this->target_id = $target_id;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getStatus(): ?ReportStatus
    {
        return $this->status;
    }

    public function setStatus(ReportStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResolver(): ?User
    {
        return $this->resolver;
    }

    public function setResolver(?User $resolver): static
    {
        $this->resolver = $resolver;

        return $this;
    }
}
