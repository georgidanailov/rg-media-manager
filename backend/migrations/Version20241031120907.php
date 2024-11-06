<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031120907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD parent_id INT DEFAULT NULL, ADD is_current_version TINYINT(1) DEFAULT 1 NOT NULL, ADD version INT NOT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C727ACA70 FOREIGN KEY (parent_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6A2CA10C727ACA70 ON media (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C727ACA70');
        $this->addSql('DROP INDEX IDX_6A2CA10C727ACA70 ON media');
        $this->addSql('ALTER TABLE media DROP parent_id, DROP is_current_version, DROP version');
    }
}
