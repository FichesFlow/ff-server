<?php

namespace App\Entity\Traits;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait DateAtTrait
{
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['deck:list'])]
    private ?DateTime $updated_at = null;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTime();
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

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
