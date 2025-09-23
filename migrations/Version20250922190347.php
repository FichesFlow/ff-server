<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922190347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_preference (id SERIAL NOT NULL, owner_id UUID NOT NULL, theme VARCHAR(255) DEFAULT NULL, language VARCHAR(255) DEFAULT NULL, daily_reminder TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cards_per_session SMALLINT NOT NULL, notif_on_level_up BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FA0E76BF7E3C61F9 ON user_preference (owner_id)');
        $this->addSql('COMMENT ON COLUMN user_preference.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_preference.daily_reminder IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_preference ADD CONSTRAINT FK_FA0E76BF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_preference DROP CONSTRAINT FK_FA0E76BF7E3C61F9');
        $this->addSql('DROP TABLE user_preference');
    }
}
