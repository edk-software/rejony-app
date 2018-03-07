<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180307163022 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE `cantiga_edk_contemplations` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `editionId` INT(11) NOT NULL , `langCode` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL , `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NULL , `value` TEXT CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE `cantiga_edk_contemplations`');
    }
}
