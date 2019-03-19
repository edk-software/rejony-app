<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190319151819 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `cantiga_project_settings`(`projectId`, `module`, `name`, `key`, `value`, `type`, `isRequired`) SELECT `id`, \'edk\', \'Enable mirror redirect\', \'edk_redirect_to_mirror\', 0, 2, 1 FROM `cantiga_projects`');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `cantiga_project_settings` WHERE `key`=  \'edk_redirect_to_mirror\'');

    }
}
