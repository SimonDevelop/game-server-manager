<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729153850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_server DROP FOREIGN KEY FK_2758783E7C5A601B');
        $this->addSql('ALTER TABLE game_server ADD CONSTRAINT FK_2758783E7C5A601B FOREIGN KEY (id_server) REFERENCES `server` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `game_server` DROP FOREIGN KEY FK_2758783E7C5A601B');
        $this->addSql('ALTER TABLE `game_server` ADD CONSTRAINT FK_2758783E7C5A601B FOREIGN KEY (id_server) REFERENCES server (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
