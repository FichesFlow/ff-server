<?php

namespace App\DataFixtures;

use App\DataFixtures\CardFixtures;
use App\DataFixtures\DeckCommentFixtures;
use App\DataFixtures\DeckFixtures;
use App\DataFixtures\RatingFixtures;
use App\DataFixtures\TagFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
            DeckFixtures::class,
            CardFixtures::class,
            RatingFixtures::class,
            DeckCommentFixtures::class,
        ];
    }
}
