<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622074208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE deck_tag (deck_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(deck_id, tag_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E72958A7111948DC ON deck_tag (deck_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E72958A7BAD26311 ON deck_tag (tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_tag.deck_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_tag.tag_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (id UUID NOT NULL, parent_tag_id UUID DEFAULT NULL, name VARCHAR(80) NOT NULL, slug VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_389B783F5C1A0D7 ON tag (parent_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN tag.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN tag.parent_tag_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN tag.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_tag ADD CONSTRAINT FK_E72958A7111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_tag ADD CONSTRAINT FK_E72958A7BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag ADD CONSTRAINT FK_389B783F5C1A0D7 FOREIGN KEY (parent_tag_id) REFERENCES tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_tag DROP CONSTRAINT FK_E72958A7111948DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_tag DROP CONSTRAINT FK_E72958A7BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag DROP CONSTRAINT FK_389B783F5C1A0D7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deck_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag
        SQL);
    }
}
