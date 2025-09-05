<?php

namespace App\EventSubscriber;

use App\Entity\CardBlock;
use App\Service\MarkdownSanitizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: CardBlock::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: CardBlock::class)]
final class CardBlockSubscriber
{
    public function __construct(private MarkdownSanitizer $ms) {}

    public function prePersist(CardBlock $cb): void
    {
        $cb->setContent($this->ms->toSafeHtml($cb->getContent()));
    }

    public function preUpdate(CardBlock $cb): void
    {
        $cb->setContent($this->ms->toSafeHtml($cb->getContent()));
    }
}
