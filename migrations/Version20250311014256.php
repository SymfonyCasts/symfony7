<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311014256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE starship_droid (starship_id INT NOT NULL, droid_id INT NOT NULL, PRIMARY KEY(starship_id, droid_id))');
        $this->addSql('CREATE INDEX IDX_1C7FBE889B24DF5 ON starship_droid (starship_id)');
        $this->addSql('CREATE INDEX IDX_1C7FBE88AB064EF ON starship_droid (droid_id)');
        $this->addSql('ALTER TABLE starship_droid ADD CONSTRAINT FK_1C7FBE889B24DF5 FOREIGN KEY (starship_id) REFERENCES starship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE starship_droid ADD CONSTRAINT FK_1C7FBE88AB064EF FOREIGN KEY (droid_id) REFERENCES droid (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE starship_droid DROP CONSTRAINT FK_1C7FBE889B24DF5');
        $this->addSql('ALTER TABLE starship_droid DROP CONSTRAINT FK_1C7FBE88AB064EF');
        $this->addSql('DROP TABLE starship_droid');
    }
}
