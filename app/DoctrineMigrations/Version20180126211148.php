<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126211148 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE cantiga_edk_routes t, (SELECT id, IF(gpsTrackFile is null,0,IF(approved,2,1)) as gpsStatus, IF(mapFile is null,0,1) as mapStatus, IF(descriptionFile is null,0,1) as descriptionStatus from cantiga_edk_routes) t1 SET   t.gpsStatus = t1.gpsStatus, t.mapStatus = t1.mapStatus, t.descriptionStatus = t1.descriptionStatus WHERE t.id = t1.id;');


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
