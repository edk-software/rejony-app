<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180302165624 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants` DROP COLUMN `participantId`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants` DROP COLUMN `reason`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants` DROP FOREIGN KEY `cantiga_edk_removed_participants_fk2`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants` DROP COLUMN `removedById`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD `firstName` varchar(30) NULL AFTER `id`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD `routeId` int(11) NULL AFTER `areaId`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD CONSTRAINT cantiga_edk_removed_participants_route_fk FOREIGN KEY (routeId) REFERENCES cantiga_edk_routes (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants` DROP COLUMN `firstName`, `routeId`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD `reason` varchar(30) NOT NULL`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD `participantId` varchar(150)	 NOT NULL`');
        $this->addSql('ALTER TABLE `cantiga_edk_removed_participants`  ADD `removedById` int(11) NOT NULL `');

    }
}
