<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240703105645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guest_event (guest_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_2E509DB39A4AA658 (guest_id), INDEX IDX_2E509DB371F7E88B (event_id), PRIMARY KEY(guest_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guest_event ADD CONSTRAINT FK_2E509DB39A4AA658 FOREIGN KEY (guest_id) REFERENCES guest (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest_event ADD CONSTRAINT FK_2E509DB371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest_event DROP FOREIGN KEY FK_2E509DB39A4AA658');
        $this->addSql('ALTER TABLE guest_event DROP FOREIGN KEY FK_2E509DB371F7E88B');
        $this->addSql('DROP TABLE guest_event');
    }
}
