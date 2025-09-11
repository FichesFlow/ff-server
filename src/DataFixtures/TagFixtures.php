<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TagFixtures extends Fixture
{
    protected $faker;
    private const TAG_REFERENCE = 'tag';
    private $parentsTags = ['Maths', 'Langues', 'Science'];

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < count($this->parentsTags); $i++) {
            $tag = new Tag();
            $tag->setName($this->parentsTags[$i]);
            $tag->setSlug(strtolower($this->parentsTags[$i]));
            $tag->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->datetime()));
            $manager->persist($tag);

            $this->addReference(self::TAG_REFERENCE . '_' . $i, $tag);
        }

        $manager->flush();

        for ($i = 3; $i < 12; $i++) {
            $tag = new Tag();
            $tag->setName($this->faker->word());
            $tag->setSlug(strtolower($this->faker->word()));
            $tag->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->datetime()));
            $parentTag = $this->getReference(self::TAG_REFERENCE . '_' . $this->faker->numberBetween(0, 2), Tag::class);
            $tag->setParentTag($parentTag);
            $manager->persist($tag);

            $this->addReference(self::TAG_REFERENCE . '_' . $i, $tag);
        }

        $manager->flush();
    }
}