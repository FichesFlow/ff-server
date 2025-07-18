<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Repository\ReviewProgressRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewProgressRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_reviewer_card', columns: ['reviewer_id', 'card_id'])]
#[ORM\Index(name: 'idx_reviewer_due_at', columns: ['reviewer_id', 'due_at'])]
#[ORM\Index(name: 'idx_card_due_at', columns: ['card_id', 'due_at'])]
#[ApiResource]
class ReviewProgress
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'reviewProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reviewer = null;

    #[ORM\ManyToOne(inversedBy: 'reviewProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, options: ['default' => '2.50'])]
    private ?float $easiness = 2.50;

    #[ORM\Column(options: ['default' => 1])]
    private ?int $interval_days = 1;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?DateTime $due_at = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?DateTime $last_review_at = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $last_score = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $total_reviews = 0;

    public function getReviewer(): ?User
    {
        return $this->reviewer;
    }

    public function setReviewer(?User $reviewer): static
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    public function getEasiness(): ?float
    {
        return $this->easiness;
    }

    public function setEasiness(float $easiness): static
    {
        $this->easiness = $easiness;

        return $this;
    }

    public function getIntervalDays(): ?int
    {
        return $this->interval_days;
    }

    public function setIntervalDays(int $interval_days): static
    {
        $this->interval_days = $interval_days;

        return $this;
    }

    public function getDueAt(): ?DateTime
    {
        return $this->due_at;
    }

    public function setDueAt(DateTime $due_at): static
    {
        $this->due_at = $due_at;

        return $this;
    }

    public function getLastReviewAt(): ?DateTime
    {
        return $this->last_review_at;
    }

    public function setLastReviewAt(?DateTime $last_review_at): static
    {
        $this->last_review_at = $last_review_at;

        return $this;
    }

    public function getLastScore(): ?int
    {
        return $this->last_score;
    }

    public function setLastScore(?int $last_score): static
    {
        $this->last_score = $last_score;

        return $this;
    }

    public function getTotalReviews(): ?int
    {
        return $this->total_reviews;
    }

    public function setTotalReviews(int $total_reviews): static
    {
        $this->total_reviews = $total_reviews;

        return $this;
    }
}
