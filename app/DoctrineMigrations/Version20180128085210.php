<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180128085210 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_users ADD COLUMN `marketingAgreementAt` int(11) DEFAULT NULL AFTER `personalDataAllowedAt`');
        $this->addSql('ALTER TABLE cantiga_user_registrations ADD COLUMN `marketingAgreement` TINYINT(1) NOT NULL DEFAULT 0 AFTER `provisionKey`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_user_registrations DROP COLUMN `marketingAgreement`');
        $this->addSql('ALTER TABLE cantiga_users DROP COLUMN `marketingAgreementAt`');
    }
}
