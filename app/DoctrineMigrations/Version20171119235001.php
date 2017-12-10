<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171119235001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_areas`
            ADD `stationaryTraining` tinyint(4) NOT NULL DEFAULT "0" AFTER `visiblePublicly`,
            ADD `contract` tinyint(4) NOT NULL DEFAULT "0" AFTER `stationaryTraining`,
            ADD `lat` decimal(18,9) DEFAULT NULL AFTER `placeId`,
            ADD `lng` decimal(18,9) DEFAULT NULL AFTER `lat`');
        $this->addSql('ALTER TABLE `cantiga_area_requests`
            ADD `lat` decimal(18,9) DEFAULT NULL AFTER `lastUpdatedAt`,
            ADD `lng` decimal(18,9) DEFAULT NULL AFTER `lat`');
        $this->addSql('ALTER TABLE `cantiga_credential_changes`
            CHANGE `requestIp` `requestIp` bigint(20) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            ADD `routePatron` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `name`,
            ADD `routeColor` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routePatron`,
            ADD `routeAuthor` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeColor`,
            ADD `routeFromDetails` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeFrom`,
            ADD `routeToDetails` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeTo`,
            ADD `createdBy` int(11) DEFAULT NULL AFTER `createdAt`,
            ADD `updatedBy` int(11) DEFAULT NULL AFTER `updatedAt`,
            ADD `approvedAt` int(11) DEFAULT NULL AFTER `approved`,
            ADD `approvedBy` int(11) DEFAULT NULL AFTER `approvedAt`,
            ADD `descriptionCreatedAt` int(11) DEFAULT NULL AFTER `descriptionFile`,
            ADD `descriptionCreatedBy` int(11) DEFAULT NULL AFTER `descriptionCreatedAt`,
            ADD `descriptionUpdatedAt` int(11) DEFAULT NULL AFTER `descriptionCreatedBy`,
            ADD `descriptionUpdatedBy` int(11) DEFAULT NULL AFTER `descriptionUpdatedAt`,
            ADD `descriptionApproved` tinyint(4) NOT NULL DEFAULT "0" AFTER `descriptionUpdatedBy`,
            ADD `descriptionApprovedAt` int(11) DEFAULT NULL AFTER `descriptionApproved`,
            ADD `descriptionApprovedBy` int(11) DEFAULT NULL AFTER `descriptionApprovedAt`,
            ADD `mapCreatedAt` int(11) DEFAULT NULL AFTER `mapFile`,
            ADD `mapCreatedBy` int(11) DEFAULT NULL AFTER `mapCreatedAt`,
            ADD `mapUpdatedAt` int(11) DEFAULT NULL AFTER `mapCreatedBy`,
            ADD `mapUpdatedBy` int(11) DEFAULT NULL AFTER `mapUpdatedAt`,
            ADD `mapApproved` tinyint(4) NOT NULL DEFAULT "0" AFTER `mapUpdatedBy`,
            ADD `mapApprovedAt` int(11) DEFAULT NULL AFTER `mapApproved`,
            ADD `mapApprovedBy` int(11) DEFAULT NULL AFTER `mapApprovedAt`,
            ADD `gpsCreatedAt` int(11) DEFAULT NULL AFTER `gpsTrackFile`,
            ADD `gpsCreatedBy` int(11) DEFAULT NULL AFTER `gpsCreatedAt`,
            ADD `gpsUpdatedAt` int(11) DEFAULT NULL AFTER `gpsCreatedBy`,
            ADD `gpsUpdatedBy` int(11) DEFAULT NULL AFTER `gpsUpdatedAt`,
            ADD `gpsApproved` tinyint(4) NOT NULL DEFAULT "0" AFTER `gpsUpdatedBy`,
            ADD `gpsApprovedAt` int(11) DEFAULT NULL AFTER `gpsApproved`,
            ADD `gpsApprovedBy` int(11) DEFAULT NULL AFTER `gpsApprovedAt`');
        $this->addSql('ALTER TABLE `cantiga_password_recovery`
            CHANGE `requestIp` `requestIp` bigint(20) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_user_registrations`
            CHANGE `requestIp` `requestIp` bigint(20) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_project_settings`
            ADD `isRequired` tinyint(4) DEFAULT "1" AFTER `extensionPoint`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_project_settings` DROP `isRequired`');
        $this->addSql('ALTER TABLE `cantiga_user_registrations`
            CHANGE `requestIp` `requestIp` int(11) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_password_recovery`
            CHANGE `requestIp` `requestIp` int(11) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            DROP `routePatron`, DROP `routeColor`, DROP `routeAuthor`, DROP `routeFromDetails`, DROP `routeToDetails`, DROP `createdBy`, DROP `updatedBy`, DROP `approvedAt`, DROP `approvedBy`, DROP `descriptionCreatedAt`, DROP `descriptionCreatedBy`, DROP `descriptionUpdatedAt`, DROP `descriptionUpdatedBy`, DROP `descriptionApproved`, DROP `descriptionApprovedAt`, DROP `descriptionApprovedBy`, DROP `mapCreatedAt`, DROP `mapCreatedBy`, DROP `mapUpdatedAt`, DROP `mapUpdatedBy`, DROP `mapApproved`, DROP `mapApprovedAt`, DROP `mapApprovedBy`, DROP `gpsCreatedAt`, DROP `gpsCreatedBy`, DROP `gpsUpdatedAt`, DROP `gpsUpdatedBy`, DROP `gpsApproved`, DROP `gpsApprovedAt`, DROP `gpsApprovedBy`');
        $this->addSql('ALTER TABLE `cantiga_credential_changes`
            CHANGE `requestIp` `requestIp` int(11) NOT NULL');
        $this->addSql('ALTER TABLE `cantiga_area_requests` DROP `lat`, DROP `lng`');
        $this->addSql('ALTER TABLE `cantiga_areas` DROP `stationaryTraining`, DROP `contract`, DROP `lat`, DROP `lng`');
    }
}
