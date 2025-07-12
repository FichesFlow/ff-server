<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250712083648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE score_event (id SERIAL NOT NULL, scorer_id UUID NOT NULL, type VARCHAR(255) NOT NULL, value INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82E2C62F43B35028 ON score_event (scorer_id)');
        $this->addSql('CREATE INDEX score_event_created_at_idx ON score_event (created_at)');
        $this->addSql('COMMENT ON COLUMN score_event.scorer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN score_event.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE score_event ADD CONSTRAINT FK_82E2C62F43B35028 FOREIGN KEY (scorer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE score_event DROP CONSTRAINT FK_82E2C62F43B35028');
        $this->addSql('DROP TABLE score_event');
    }
}
