<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Repository\ReviewEventRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ReviewEventRepository::class)]
#[ApiResource]
class ReviewEvent
{
    use UuidTrait;

    #[ORM\ManyToOne(inversedBy: 'reviewEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReviewSession $session = null;

    #[ORM\ManyToOne(inversedBy: 'reviewEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reviewer = null;

    #[ORM\ManyToOne(inversedBy: 'reviewEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTime $reviewed_at = null;

    public function getSession(): ?ReviewSession
    {
        return $this->session;
    }

    public function setSession(?ReviewSession $session): static
    {
        $this->session = $session;

        return $this;
    }

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

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewed_at;
    }

    public function setReviewedAt(DateTime $reviewed_at): static
    {
        $this->reviewed_at = $reviewed_at;

        return $this;
    }
}
