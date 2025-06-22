<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622080225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE card (id UUID NOT NULL, deck_id UUID NOT NULL, position SMALLINT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_161498D3111948DC ON card (deck_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.deck_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_block (id UUID NOT NULL, card_side_id UUID NOT NULL, content_type VARCHAR(255) NOT NULL, content JSONB NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_BB2F66E4C1470BB2 ON card_block (card_side_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_block.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_block.card_side_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_side (id UUID NOT NULL, card_id UUID NOT NULL, side VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CFB219174ACC9A20 ON card_side (card_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_side.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_side.card_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card ADD CONSTRAINT FK_161498D3111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_block ADD CONSTRAINT FK_BB2F66E4C1470BB2 FOREIGN KEY (card_side_id) REFERENCES card_side (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_side ADD CONSTRAINT FK_CFB219174ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card DROP CONSTRAINT FK_161498D3111948DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_block DROP CONSTRAINT FK_BB2F66E4C1470BB2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_side DROP CONSTRAINT FK_CFB219174ACC9A20
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card_block
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card_side
        SQL);
    }
}
