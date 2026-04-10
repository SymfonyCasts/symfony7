<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410171923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__starship AS SELECT id, name, class, captain, status, arrived_at, slug, created_at, updated_at FROM starship');
        $this->addSql('DROP TABLE starship');
        $this->addSql('CREATE TABLE starship (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, captain VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, arrived_at DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_C414E64AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO starship (id, name, class, captain, status, arrived_at, slug, created_at, updated_at) SELECT id, name, class, captain, status, arrived_at, slug, created_at, updated_at FROM __temp__starship');
        $this->addSql('DROP TABLE __temp__starship');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C414E64A989D9B62 ON starship (slug)');
        $this->addSql('CREATE INDEX IDX_C414E64AB03A8386 ON starship (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__starship AS SELECT id, name, class, captain, status, arrived_at, slug, created_at, updated_at FROM starship');
        $this->addSql('DROP TABLE starship');
        $this->addSql('CREATE TABLE starship (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, captain VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, arrived_at DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO starship (id, name, class, captain, status, arrived_at, slug, created_at, updated_at) SELECT id, name, class, captain, status, arrived_at, slug, created_at, updated_at FROM __temp__starship');
        $this->addSql('DROP TABLE __temp__starship');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C414E64A989D9B62 ON starship (slug)');
    }
}
