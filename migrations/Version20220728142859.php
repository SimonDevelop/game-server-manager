<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728142859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_game_server (user_id INT NOT NULL, game_server_id INT NOT NULL, INDEX IDX_8E43B1ABA76ED395 (user_id), INDEX IDX_8E43B1ABCAE227B5 (game_server_id), PRIMARY KEY(user_id, game_server_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_game_server ADD CONSTRAINT FK_8E43B1ABA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_game_server ADD CONSTRAINT FK_8E43B1ABCAE227B5 FOREIGN KEY (game_server_id) REFERENCES `game_server` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_game_server');
    }
}
