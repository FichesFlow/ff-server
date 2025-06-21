<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250621181818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE badge (id UUID NOT NULL, code VARCHAR(16) NOT NULL, name VARCHAR(64) NOT NULL, description TEXT DEFAULT NULL, icon_url TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN badge.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_badge (id SERIAL NOT NULL, owner_id UUID NOT NULL, badge_id UUID NOT NULL, awarded_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C32B3457E3C61F9 ON user_badge (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C32B345F7A2C2FC ON user_badge (badge_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_badge.owner_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_badge.badge_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_badge.awarded_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B3457E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge DROP CONSTRAINT FK_1C32B3457E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge DROP CONSTRAINT FK_1C32B345F7A2C2FC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_badge
        SQL);
    }
}
