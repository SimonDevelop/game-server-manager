<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211026101553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_server ADD id_server INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_server ADD CONSTRAINT FK_2758783E7C5A601B FOREIGN KEY (id_server) REFERENCES server (id)');
        $this->addSql('CREATE INDEX IDX_2758783E7C5A601B ON game_server (id_server)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_server DROP FOREIGN KEY FK_2758783E7C5A601B');
        $this->addSql('DROP INDEX IDX_2758783E7C5A601B ON game_server');
        $this->addSql('ALTER TABLE game_server DROP id_server');
    }
}
