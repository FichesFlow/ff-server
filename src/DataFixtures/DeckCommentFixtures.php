<?php

namespace App\DataFixtures;

use App\Entity\DeckComment;
use App\Repository\DeckRepository;
use App\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DeckCommentFixtures extends Fixture implements DependentFixtureInterface
{
    private const string COMMENT_REFERENCE = 'deck_comment';
    private $faker;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DeckRepository $deckRepository
    )
    {
        $this->faker = Factory::create();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();
        $decks = $this->deckRepository->findAll();

        $commentTemplates = [
            "Great deck! Really helps with learning {subject}.",
            "This is exactly what I was looking for. Thanks for sharing!",
            "Could you add more examples for the difficult concepts?",
            "Amazing work! The explanations are very clear.",
            "I've been using this deck for weeks and my progress is fantastic.",
            "Some cards could use better formatting, but overall very useful.",
            "Perfect for beginners in {subject}. Highly recommended!",
            "The difficulty progression is well thought out.",
            "Would love to see more advanced topics added to this deck.",
            "Clear, concise, and comprehensive. Excellent work!",
            "This deck helped me pass my exam. Thank you so much!",
            "The visual aids in some cards are really helpful.",
            "Could benefit from audio pronunciation for language cards.",
            "One of the best decks I've used on this topic.",
            "The spacing between difficulty levels is perfect for learning."
        ];

        // Generate 100 comments across different decks
        for ($i = 0; $i < 100; $i++) {
            $deck = $decks[array_rand($decks)];
            $commenter = $users[array_rand($users)];

            // Skip if user is commenting on their own deck (optional rule)
            if ($deck->getOwner() === $commenter && rand(0, 10) > 3) {
                continue;
            }

            $comment = new DeckComment();
            $comment->setDeck($deck);
            $comment->setCommenter($commenter);

            // Use template or generate random comment
            if (rand(0, 1)) {
                $template = $commentTemplates[array_rand($commentTemplates)];
                $body = str_replace('{subject}', $this->getSubjectFromTitle($deck->getTitle()), $template);
            } else {
                $body = $this->faker->paragraph(rand(1, 3));
            }

            $comment->setBody($body);

            $dateRandom = $this->faker->dateTimeBetween('-6 months', 'now');
            $createdAt = DateTimeImmutable::createFromMutable($dateRandom);
            $comment->setCreatedAt($createdAt);
            $comment->setUpdatedAt($dateRandom);

            $manager->persist($comment);
            $this->addReference(self::COMMENT_REFERENCE . '_' . $i, $comment);
        }

        $manager->flush();
    }

    private function getSubjectFromTitle(string $title): string
    {
        $subjects = ['mathematics', 'science', 'history', 'language', 'literature', 'programming'];
        return $subjects[array_rand($subjects)];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            DeckFixtures::class,
        ];
    }
}
