<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717134230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_progress (id UUID NOT NULL, reviewer_id UUID NOT NULL, card_id UUID NOT NULL, easiness NUMERIC(3, 2) DEFAULT \'2.50\' NOT NULL, interval_days INT DEFAULT 1 NOT NULL, due_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, last_review_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, last_score SMALLINT DEFAULT NULL, total_reviews INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1437B4D70574616 ON review_progress (reviewer_id)');
        $this->addSql('CREATE INDEX IDX_1437B4D4ACC9A20 ON review_progress (card_id)');
        $this->addSql('CREATE INDEX idx_reviewer_due_at ON review_progress (reviewer_id, due_at)');
        $this->addSql('CREATE UNIQUE INDEX unique_reviewer_card ON review_progress (reviewer_id, card_id)');
        $this->addSql('COMMENT ON COLUMN review_progress.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_progress.reviewer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_progress.card_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE review_progress ADD CONSTRAINT FK_1437B4D70574616 FOREIGN KEY (reviewer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_progress ADD CONSTRAINT FK_1437B4D4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_session ADD origin VARCHAR(255) DEFAULT \'queue\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_progress DROP CONSTRAINT FK_1437B4D70574616');
        $this->addSql('ALTER TABLE review_progress DROP CONSTRAINT FK_1437B4D4ACC9A20');
        $this->addSql('DROP TABLE review_progress');
        $this->addSql('ALTER TABLE review_session DROP origin');
    }
}
