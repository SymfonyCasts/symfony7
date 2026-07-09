<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709172207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE droid (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, primary_function VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE starship (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, captain VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, arrived_at DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C414E64A989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE starship_droid (id INT AUTO_INCREMENT NOT NULL, assigned_at DATETIME NOT NULL, droid_id INT NOT NULL, starship_id INT NOT NULL, INDEX IDX_1C7FBE88AB064EF (droid_id), INDEX IDX_1C7FBE889B24DF5 (starship_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE starship_part (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INT NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, starship_id INT NOT NULL, INDEX IDX_41C447379B24DF5 (starship_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE starship_droid ADD CONSTRAINT FK_1C7FBE88AB064EF FOREIGN KEY (droid_id) REFERENCES droid (id)');
        $this->addSql('ALTER TABLE starship_droid ADD CONSTRAINT FK_1C7FBE889B24DF5 FOREIGN KEY (starship_id) REFERENCES starship (id)');
        $this->addSql('ALTER TABLE starship_part ADD CONSTRAINT FK_41C447379B24DF5 FOREIGN KEY (starship_id) REFERENCES starship (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE starship_droid DROP FOREIGN KEY FK_1C7FBE88AB064EF');
        $this->addSql('ALTER TABLE starship_droid DROP FOREIGN KEY FK_1C7FBE889B24DF5');
        $this->addSql('ALTER TABLE starship_part DROP FOREIGN KEY FK_41C447379B24DF5');
        $this->addSql('DROP TABLE droid');
        $this->addSql('DROP TABLE starship');
        $this->addSql('DROP TABLE starship_droid');
        $this->addSql('DROP TABLE starship_part');
        $this->addSql('DROP TABLE user');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
