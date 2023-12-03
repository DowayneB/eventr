<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231203200913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD event_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7401B253C FOREIGN KEY (event_type_id) REFERENCES event_type (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7401B253C ON event (event_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7401B253C');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7401B253C ON event');
        $this->addSql('ALTER TABLE event DROP event_type_id');
    }
}
