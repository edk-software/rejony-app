<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180220064015 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_areas`
            CHANGE `name` `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            CHANGE `slug` `slug` varchar(12) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            CHANGE `groupName` `groupName` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
            CHANGE `customData` `customData` text CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_polish_ci');

        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            CHANGE `routeObstacles` `routeObstacles` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
            CHANGE `descriptionFile` `descriptionFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
            CHANGE `mapFile` `mapFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
            CHANGE `gpsTrackFile` `gpsTrackFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            CHANGE `publicAccessSlug` `publicAccessSlug` varchar(40) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_polish_ci');

        $this->addSql('ALTER TABLE `cantiga_territories`
            CHANGE `name` `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            CHANGE `locale` `locale` varchar(10) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_polish_ci');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_areas`
            CHANGE `name` `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            CHANGE `slug` `slug` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            CHANGE `groupName` `groupName` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            CHANGE `customData` `customData` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci');

        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            CHANGE `routeObstacles` `routeObstacles` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            CHANGE `descriptionFile` `descriptionFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            CHANGE `mapFile` `mapFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            CHANGE `gpsTrackFile` `gpsTrackFile` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            CHANGE `publicAccessSlug` `publicAccessSlug` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci');

        $this->addSql('ALTER TABLE `cantiga_territories`
            CHANGE `name` `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            CHANGE `locale` `locale` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci');
    }
}
