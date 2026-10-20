<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261001140901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add user.username column';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, name, last_login, starship_id, disabled_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, starship_id INTEGER DEFAULT NULL, disabled_at DATETIME DEFAULT NULL, username VARCHAR(255) NOT NULL, CONSTRAINT FK_8D93D6499B24DF5 FOREIGN KEY (starship_id) REFERENCES starship (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, email, roles, password, name, last_login, starship_id, disabled_at) SELECT id, email, roles, password, name, last_login, starship_id, disabled_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE INDEX IDX_8D93D6499B24DF5 ON user (starship_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, name, last_login, disabled_at, starship_id FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, disabled_at DATETIME DEFAULT NULL, starship_id INTEGER DEFAULT NULL, CONSTRAINT FK_8D93D6499B24DF5 FOREIGN KEY (starship_id) REFERENCES starship (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, email, roles, password, name, last_login, disabled_at, starship_id) SELECT id, email, roles, password, name, last_login, disabled_at, starship_id FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX IDX_8D93D6499B24DF5 ON user (starship_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
