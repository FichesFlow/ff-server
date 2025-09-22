<?php

namespace App\Repository;

use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }

    public function findTopRated(int $limit): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.visibility = :pub')
            ->andWhere('d.status = :publi')
            ->andWhere('d.rating_count >= 5')
            ->setParameters(new ArrayCollection([
                new Parameter('pub', 'public'),
                new Parameter('publi', 'published')
            ]))
            ->orderBy('d.rating_avg', 'DESC')
            ->addOrderBy('d.rating_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRandomPublicPublished(int $limit, array $excludeIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.visibility = :pub')
            ->andWhere('d.status = :publi')
            ->setParameters(new ArrayCollection([
                new Parameter('pub', 'public'),
                new Parameter('publi', 'published')
            ]));

        if ($excludeIds) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('d.id', ':excl'))
                ->setParameter('excl', $excludeIds);
        }

        return $queryBuilder->orderBy('RANDOM()')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByTitle(string $query, int $limit = 10): array
    {
        $searchTerm = trim($query);
        if (empty($searchTerm)) {
            return [];
        }

        // Convert to lowercase for case-insensitive search
        $lowerSearchTerm = strtolower($searchTerm);
        $exactMatch = $lowerSearchTerm;
        $startsWithMatch = $lowerSearchTerm . '%';
        $containsMatch = '%' . $lowerSearchTerm . '%';

        return $this->createQueryBuilder('d')
            ->where('d.visibility = :visibility')
            ->andWhere('d.status = :status')
            ->andWhere('(LOWER(d.title) LIKE :exact OR LOWER(d.title) LIKE :startsWith OR LOWER(d.title) LIKE :contains)')
            ->setParameters(new ArrayCollection([
                new Parameter('visibility', 'public'),
                new Parameter('status', 'published'),
                new Parameter('exact', $exactMatch),
                new Parameter('startsWith', $startsWithMatch),
                new Parameter('contains', $containsMatch)
            ]))
            ->addSelect('
                CASE
                    WHEN LOWER(d.title) = :searchTerm THEN 1
                    WHEN LOWER(d.title) LIKE :startsWithTerm THEN 2
                    WHEN LOWER(d.title) LIKE :containsTerm THEN 3
                    ELSE 4
                END as HIDDEN relevance
            ')
            ->setParameter('searchTerm', $lowerSearchTerm)
            ->setParameter('startsWithTerm', $startsWithMatch)
            ->setParameter('containsTerm', $containsMatch)
            ->addOrderBy('relevance', 'ASC')
            ->addOrderBy('d.rating_avg', 'DESC')
            ->addOrderBy('d.rating_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByTitleAndDescription(string $query, int $limit = 15): array
    {
        $searchTerm = trim($query);
        if (empty($searchTerm)) {
            return [];
        }

        $lowerSearchTerm = strtolower($searchTerm);
        $containsMatch = '%' . $lowerSearchTerm . '%';

        return $this->createQueryBuilder('d')
            ->where('d.visibility = :visibility')
            ->andWhere('d.status = :status')
            ->andWhere('(LOWER(d.title) LIKE :contains OR LOWER(d.description) LIKE :contains)')
            ->setParameters(new ArrayCollection([
                new Parameter('visibility', 'public'),
                new Parameter('status', 'published'),
                new Parameter('contains', $containsMatch)
            ]))
            ->addSelect('
                CASE
                    WHEN LOWER(d.title) LIKE :titleMatch THEN 1
                    WHEN LOWER(d.description) LIKE :descMatch THEN 2
                    ELSE 3
                END as HIDDEN relevance
            ')
            ->setParameter('titleMatch', $containsMatch)
            ->setParameter('descMatch', $containsMatch)
            ->addOrderBy('relevance', 'ASC')
            ->addOrderBy('d.rating_avg', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Deck[] Returns an array of Deck objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Deck
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
