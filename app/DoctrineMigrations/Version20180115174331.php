<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180115174331 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cantiga_area_comments (id int(11) NOT NULL AUTO_INCREMENT, areaId int(11) NOT NULL, userId int(11) NOT NULL, createdAt int(11) NOT NULL, message  varchar(500) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cantiga_area_comments ADD CONSTRAINT cantiga_area_comments_ibfk_1 FOREIGN KEY (areaId) REFERENCES cantiga_areas (id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE cantiga_area_comments ADD CONSTRAINT cantiga_area_comments_comments_ibfk_2 FOREIGN KEY (userId) REFERENCES cantiga_users (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_area_comments DROP FOREIGN KEY cantiga_area_comments_comments_ibfk_2');
        $this->addSql('ALTER TABLE cantiga_area_comments DROP FOREIGN KEY cantiga_area_comments_ibfk_1');
        $this->addSql('DROP TABLE cantiga_area_comments');


    }
}
