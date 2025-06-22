<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622083659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE INDEX badge_idx_code ON badge (code)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX card_idx_deck_position ON card (deck_id, position)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX card_block_idx_content_type ON card_block (content_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX card_block_idx_content ON card_block (content)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX card_side_idx_card_side ON card_side (card_id, side)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX deck_idx_visibility_status ON deck (visibility, status)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX deck_idx_rating_avg_count ON deck (rating_avg, rating_count)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX deck_rating_unique ON deck_rating (deck_id, rater_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX report_idx_status_target ON report (status, target_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX tag_idx_slug ON tag (slug)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX deck_idx_visibility_status
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX deck_idx_rating_avg_count
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_IDENTIFIER_USERNAME
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX card_block_idx_content_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX card_block_idx_content
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX card_side_idx_card_side
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX tag_idx_slug
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX report_idx_status_target
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX deck_rating_unique
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX badge_idx_code
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX card_idx_deck_position
        SQL);
    }
}
