<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200226200000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            ADD `pathCoordinates` longtext DEFAULT NULL AFTER `elevationCharacteristic`,
            ADD `stations` text DEFAULT NULL AFTER `pathCoordinates`,
            ADD `pathStartLat` decimal(18,9) DEFAULT NULL AFTER `stations`,
            ADD `pathStartLng` decimal(18,9) DEFAULT NULL AFTER `pathStartLat`,
            ADD `pathEndLat` decimal(18,9) DEFAULT NULL AFTER `pathStartLng`,
            ADD `pathEndLng` decimal(18,9) DEFAULT NULL AFTER `pathEndLat`,
            ADD `pathAvgLat` decimal(18,9) DEFAULT NULL AFTER `pathEndLng`,
            ADD `pathAvgLng` decimal(18,9) DEFAULT NULL AFTER `pathAvgLat`');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `cantiga_edk_routes`
            DROP COLUMN `pathCoordinates`,
            DROP COLUMN `stations`,
            DROP COLUMN `pathStartLat`,
            DROP COLUMN `pathStartLng`,
            DROP COLUMN `pathEndLat`,
            DROP COLUMN `pathEndLng`,
            DROP COLUMN `pathAvgLat`,
            DROP COLUMN `pathAvgLng`');
    }
}
