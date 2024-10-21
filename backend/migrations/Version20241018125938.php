<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018125938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_media (tag_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_48C0B80ABAD26311 (tag_id), INDEX IDX_48C0B80AEA9FDD75 (media_id), PRIMARY KEY(tag_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_media ADD CONSTRAINT FK_48C0B80ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_media ADD CONSTRAINT FK_48C0B80AEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_media DROP FOREIGN KEY FK_48C0B80ABAD26311');
        $this->addSql('ALTER TABLE tag_media DROP FOREIGN KEY FK_48C0B80AEA9FDD75');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_media');
    }
}
