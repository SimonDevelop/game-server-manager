<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727152202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `game_server` (id INT AUTO_INCREMENT NOT NULL, id_server INT DEFAULT NULL, name VARCHAR(255) NOT NULL, command_start VARCHAR(255) NOT NULL, command_update VARCHAR(255) DEFAULT NULL, command_stop VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, game_type INT NOT NULL, state_type INT NOT NULL, installed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2758783E7C5A601B (id_server), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `server` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, ip VARCHAR(16) NOT NULL, port VARCHAR(5) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_connection DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `game_server` ADD CONSTRAINT FK_2758783E7C5A601B FOREIGN KEY (id_server) REFERENCES `server` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `game_server` DROP FOREIGN KEY FK_2758783E7C5A601B');
        $this->addSql('DROP TABLE `game_server`');
        $this->addSql('DROP TABLE `server`');
        $this->addSql('DROP TABLE `user`');
    }
}
