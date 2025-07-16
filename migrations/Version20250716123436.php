<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716123436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_event (id UUID NOT NULL, session_id UUID NOT NULL, reviewer_id UUID NOT NULL, card_id UUID NOT NULL, score SMALLINT NOT NULL, reviewed_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_282026BC613FECDF ON review_event (session_id)');
        $this->addSql('CREATE INDEX IDX_282026BC70574616 ON review_event (reviewer_id)');
        $this->addSql('CREATE INDEX IDX_282026BC4ACC9A20 ON review_event (card_id)');
        $this->addSql('COMMENT ON COLUMN review_event.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_event.session_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_event.reviewer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_event.card_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE review_session (id UUID NOT NULL, reviewer_id UUID NOT NULL, deck_id UUID DEFAULT NULL, mode VARCHAR(255) NOT NULL, started_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, cards_seen INT DEFAULT 0 NOT NULL, success_pct NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, xp_gained INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4BAA612370574616 ON review_session (reviewer_id)');
        $this->addSql('CREATE INDEX IDX_4BAA6123111948DC ON review_session (deck_id)');
        $this->addSql('COMMENT ON COLUMN review_session.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_session.reviewer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_session.deck_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review_session.started_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN review_session.finished_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE review_event ADD CONSTRAINT FK_282026BC613FECDF FOREIGN KEY (session_id) REFERENCES review_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_event ADD CONSTRAINT FK_282026BC70574616 FOREIGN KEY (reviewer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_event ADD CONSTRAINT FK_282026BC4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_session ADD CONSTRAINT FK_4BAA612370574616 FOREIGN KEY (reviewer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_session ADD CONSTRAINT FK_4BAA6123111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_event DROP CONSTRAINT FK_282026BC613FECDF');
        $this->addSql('ALTER TABLE review_event DROP CONSTRAINT FK_282026BC70574616');
        $this->addSql('ALTER TABLE review_event DROP CONSTRAINT FK_282026BC4ACC9A20');
        $this->addSql('ALTER TABLE review_session DROP CONSTRAINT FK_4BAA612370574616');
        $this->addSql('ALTER TABLE review_session DROP CONSTRAINT FK_4BAA6123111948DC');
        $this->addSql('DROP TABLE review_event');
        $this->addSql('DROP TABLE review_session');
    }
}
