<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001124449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, file_name VARCHAR(255) NOT NULL, storage_path VARCHAR(500) NOT NULL, file_size INT NOT NULL, file_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, thumbnail_path VARCHAR(500) DEFAULT NULL, INDEX IDX_6A2CA10C9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE metadata (id INT AUTO_INCREMENT NOT NULL, file_id_id INT DEFAULT NULL, data_type VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_4F143414D5C72E60 (file_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT FK_4F143414D5C72E60 FOREIGN KEY (file_id_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C9D86650F');
        $this->addSql('ALTER TABLE metadata DROP FOREIGN KEY FK_4F143414D5C72E60');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE metadata');
    }
}
