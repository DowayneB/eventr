<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225183702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_ACB79A35A76ED395 ON guest (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A35A76ED395');
        $this->addSql('DROP INDEX IDX_ACB79A35A76ED395 ON guest');
        $this->addSql('ALTER TABLE guest DROP user_id');
    }
}
