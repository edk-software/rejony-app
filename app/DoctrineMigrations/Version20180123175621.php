<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180123175621 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE VIEW `routes_summary`  AS  select `cantiga_edk_routes`.`areaId` AS `areaId`,count(`cantiga_edk_routes`.`id`) AS `routesCount`,sum(`cantiga_edk_routes`.`approved`) AS `routesApproved`,sum(if(isnull(`cantiga_edk_routes`.`gpsTrackFile`),0,1)) AS `gpsCount`,sum(`cantiga_edk_routes`.`gpsApproved`) AS `gpsApproved`,sum(if(isnull(`cantiga_edk_routes`.`descriptionFile`),0,1)) AS `descriptionCount`,sum(if(isnull(`cantiga_edk_routes`.`mapFile`),0,1)) AS `mapCount`,sum(`cantiga_edk_routes`.`descriptionApproved`) AS `descriptionApproved`,sum(`cantiga_edk_routes`.`mapApproved`) AS `mapApproved`,sum(if(((`cantiga_edk_routes`.`approved` = 1) and (`cantiga_edk_routes`.`mapApproved` = 1) and (`cantiga_edk_routes`.`descriptionApproved` = 1)),1,0)) AS `routesCertification` from `cantiga_edk_routes` group by `cantiga_edk_routes`.`areaId`');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP VIEW `routes_summary`');

    }
}
