<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126181652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `gpsApproved` `gpsStatus` INT  DEFAULT 0;');
        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `descriptionApproved` `descriptionStatus` INT  DEFAULT 0;');
        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `mapApproved` `mapStatus` INT  DEFAULT 0;');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `gpsStatus` `gpsApproved` INT  DEFAULT 0;');
        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `descriptionStatus` `descriptionApproved` INT  DEFAULT 0;');
        $this->addSql('ALTER TABLE `cantiga_edk_routes` CHANGE `mapStatus` `mapApproved` INT  DEFAULT 0;');

    }
}
