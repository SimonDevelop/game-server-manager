<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729135730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log ADD game_server_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5CAE227B5 FOREIGN KEY (game_server_id) REFERENCES `game_server` (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5CAE227B5 ON log (game_server_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5CAE227B5');
        $this->addSql('DROP INDEX IDX_8F3F68C5CAE227B5 ON log');
        $this->addSql('ALTER TABLE log DROP game_server_id');
    }
}
