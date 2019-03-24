<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190320131033 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `cantiga_project_settings`(`projectId`, `module`, `name`, `key`, `value`, `type`, `isRequired`) SELECT `id`, \'edk\', \'Percentage of profile replenishment for active area\', \'edk_published_percent\', 0, 1, 1 FROM `cantiga_projects`');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `cantiga_project_settings` WHERE `key`=  \'edk_published_percent\'');

    }
}
