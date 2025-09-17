<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Deck;
use App\Enum\CardSides;
use Doctrine\ORM\QueryBuilder;

final class FirstCardExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, QueryResultCollectionExtensionInterface
{

    public function applyToCollection(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        ?Operation                  $operation = null,
        array                       $context = []
    ): void
    {
        $this->addFirstCardData($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function addFirstCardData(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass
    ): void
    {
        if (Deck::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('cb.content')
            ->from('App\Entity\CardBlock', 'cb')
            ->join('cb.card_side', 'cs')
            ->join('cs.card', 'c')
            ->where('c.deck = ' . $rootAlias . '.id')
            ->andWhere('c.position = 0')
            ->andWhere('cs.side = \'' . CardSides::FRONT->value . '\'')
            ->setMaxResults(1)
            ->getDQL();

        $queryBuilder->addSelect('(' . $subquery . ') AS firstCardFront');
    }

    public function applyToItem(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        array                       $identifiers,
        ?Operation                  $operation = null,
        array                       $context = []
    ): void
    {
        $this->addFirstCardData($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function getResult(QueryBuilder $queryBuilder, ?string $resourceClass = null, ?Operation $operation = null, array $context = []): iterable
    {
        if (Deck::class !== $resourceClass) {
            return [];
        }

        $result = $queryBuilder->getQuery()->getResult();

        if (empty($result)) {
            return [];
        }

        if (is_array($result[0]) && isset($result[0][0])) {
            foreach ($result as &$item) {
                if (is_array($item) && $item[0] instanceof Deck) {
                    $item[0]->setFirstCardFront($item['firstCardFront']);
                    $item = $item[0];
                }
            }
        }

        return $result;
    }


    public function supportsResult(string $resourceClass, ?Operation $operation = null, array $context = []): bool
    {
        return Deck::class === $resourceClass;
    }
}
