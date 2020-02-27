<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200227043602 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `meditation_project` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(256) NULL DEFAULT NULL,
            `updatedAt`  int(11) UNSIGNED NULL DEFAULT NULL,
            `editionYear` int(11) UNSIGNED NULL DEFAULT NULL,
            `type` varchar(256) NULL DEFAULT NULL,
            PRIMARY KEY(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addSql('CREATE TABLE `meditation_languages` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `languageName` varchar(256) NULL DEFAULT NULL,
            `languageCode` varchar(256) NULL DEFAULT NULL,
            `title` varchar(256) NULL DEFAULT NULL,
            `author` varchar(256) NULL DEFAULT NULL,
            `authorBio` varchar(256) NULL DEFAULT NULL,
            `meditationProjectId`  int(11) UNSIGNED NOT NULL,
            PRIMARY KEY(`id`),
            FOREIGN KEY (`meditationProjectId`) REFERENCES `meditation_project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addSql('CREATE TABLE `meditation_station` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` varchar(256) NULL DEFAULT NULL,
            `author` varchar(256) NULL DEFAULT NULL,
            `authorBio` varchar(256) NULL DEFAULT NULL,
            `stationId`  int(11) UNSIGNED NULL DEFAULT NULL,
            `placeId` varchar(256) NULL DEFAULT NULL,
            `audioFileUrl` varchar(500) NULL DEFAULT NULL,
            `meditationText` text NOT NULL,
            `meditationId`  int(11) UNSIGNED NOT NULL,
            PRIMARY KEY(`id`),
            FOREIGN KEY (`meditationId`) REFERENCES `meditation_languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

    }
        

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `meditation_project`');
        $this->addSql('DROP TABLE `meditation_languages`');
        $this->addSql('DROP TABLE `meditation_station`');
    }
}
