<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025131313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD command_update VARCHAR(255) NOT NULL, ADD state_type INT NOT NULL');
        $this->addSql('ALTER TABLE server ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user ADD active TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP command_update, DROP state_type');
        $this->addSql('ALTER TABLE server DROP created_at');
        $this->addSql('ALTER TABLE user DROP active, DROP created_at');
    }
}
