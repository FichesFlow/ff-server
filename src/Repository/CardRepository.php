<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function findNeverSeenCardsInDeck(User $user, Deck $deck, int $limit, array $excludeIds = []): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\ReviewProgress', 'rp', 'WITH', 'rp.card = c AND rp.reviewer = :user')
            ->andWhere('c.deck = :deck')
            ->andWhere('rp.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('deck', $deck)
            ->setMaxResults($limit);

        if (!empty($excludeIds)) {
            $qb->andWhere('c.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds);
        }

        return $qb->getQuery()->getResult();
    }
}
