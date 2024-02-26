<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225185852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest ADD guest_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_ACB79A35E7AB17D9 ON guest (guest_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A35E7AB17D9');
        $this->addSql('DROP INDEX IDX_ACB79A35E7AB17D9 ON guest');
        $this->addSql('ALTER TABLE guest DROP guest_user_id');
    }
}
