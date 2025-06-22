<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622081334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE deck_comment (id UUID NOT NULL, deck_id UUID NOT NULL, commenter_id UUID DEFAULT NULL, body TEXT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_10FA6710111948DC ON deck_comment (deck_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_10FA6710B4D5A9E2 ON deck_comment (commenter_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_comment.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_comment.deck_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_comment.commenter_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE deck_rating (id UUID NOT NULL, deck_id UUID NOT NULL, rater_id UUID NOT NULL, rating SMALLINT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E8FD8431111948DC ON deck_rating (deck_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E8FD84313FC1CD0A ON deck_rating (rater_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_rating.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_rating.deck_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN deck_rating.rater_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_comment ADD CONSTRAINT FK_10FA6710111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_comment ADD CONSTRAINT FK_10FA6710B4D5A9E2 FOREIGN KEY (commenter_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_rating ADD CONSTRAINT FK_E8FD8431111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_rating ADD CONSTRAINT FK_E8FD84313FC1CD0A FOREIGN KEY (rater_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_comment DROP CONSTRAINT FK_10FA6710111948DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_comment DROP CONSTRAINT FK_10FA6710B4D5A9E2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_rating DROP CONSTRAINT FK_E8FD8431111948DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck_rating DROP CONSTRAINT FK_E8FD84313FC1CD0A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deck_comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deck_rating
        SQL);
    }
}
