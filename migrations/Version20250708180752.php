<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708180752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX card_block_idx_content_type');
        $this->addSql('ALTER TABLE card_block DROP content_type');
        $this->addSql('ALTER TABLE card_block ALTER content TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_block ADD content_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE card_block ALTER content TYPE JSONB');
        $this->addSql('CREATE INDEX card_block_idx_content_type ON card_block (content_type)');
    }
}
