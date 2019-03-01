<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190209165733 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `cantiga_agreements` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `projectId` int(11) UNSIGNED NULL DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `content` text NULL DEFAULT NULL,
            `url` varchar(255) NULL DEFAULT NULL,
            `summary` text NOT NULL,
            `createdAt` int(11) UNSIGNED NOT NULL,
            `createdBy` int(11) UNSIGNED NOT NULL,
            `updatedAt` int(11) UNSIGNED NULL DEFAULT NULL,
            `updatedBy` int(11) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX (`projectId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_agreements_signatures` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `agreementId` int(11) UNSIGNED NOT NULL,
            `signerId` int(11) UNSIGNED NOT NULL,
            `projectId` int(11) UNSIGNED NOT NULL,
            `firstName` varchar(64) NULL DEFAULT NULL,
            `lastName` varchar(64) NULL DEFAULT NULL,
            `town` varchar(64) NULL DEFAULT NULL,
            `zipCode` char(6) NULL DEFAULT NULL,
            `street` varchar(64) NULL DEFAULT NULL,
            `houseNo` varchar(16) NULL DEFAULT NULL,
            `flatNo` varchar(16) NULL DEFAULT NULL,
            `pesel` char(11) NULL DEFAULT NULL,
            `placeOfBirth` varchar(64) NULL DEFAULT NULL,
            `dateOfBirth` date NULL DEFAULT NULL,
            `signedAt` int(11) UNSIGNED NULL DEFAULT NULL,
            `createdAt` int(11) UNSIGNED NOT NULL,
            `createdBy` int(11) UNSIGNED NOT NULL,
            `updatedAt` int(11) UNSIGNED NULL DEFAULT NULL,
            `updatedBy` int(11) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `signerInProject` UNIQUE (`agreementId`, `signerId`, `projectId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `cantiga_agreements_signatures`');
        $this->addSql('DROP TABLE `cantiga_agreements`');
    }
}
