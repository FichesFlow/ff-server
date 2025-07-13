<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713082300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_queue (id UUID NOT NULL, owner_id UUID NOT NULL, deck_id UUID NOT NULL, priority VARCHAR(255) DEFAULT \'normal\' NOT NULL, added_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6C7353787E3C61F9 ON review_queue (owner_id)');
        $this->addSql('CREATE INDEX IDX_6C735378111948DC ON review_queue (deck_id)');
        $this->addSql('CREATE UNIQUE INDEX owner_deck_unique ON review_queue (owner_id, deck_id)');
        $this->addSql('COMMENT ON COLUMN review_queue.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_queue.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_queue.deck_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_queue.added_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE review_queue ADD CONSTRAINT FK_6C7353787E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_queue ADD CONSTRAINT FK_6C735378111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_queue DROP CONSTRAINT FK_6C7353787E3C61F9');
        $this->addSql('ALTER TABLE review_queue DROP CONSTRAINT FK_6C735378111948DC');
        $this->addSql('DROP TABLE review_queue');
    }
}
