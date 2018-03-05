<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180305205836 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `cantiga_edk_participants` CHANGE `sex` `sex` TINYINT(1) NULL, CHANGE `howManyTimes` `howManyTimes` TINYINT(4) NULL, CHANGE `whereLearnt` `whereLearnt` TINYINT(4) NULL DEFAULT 0;');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `cantiga_edk_participants` CHANGE `sex` `sex` TINYINT(1) NOT NULL, CHANGE `howManyTimes` `howManyTimes` TINYINT(4) NOT NULL, CHANGE `whereLearnt` `whereLearnt` TINYINT(4) NOT NULL DEFAULT 1;');

    }
}
