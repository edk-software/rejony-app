<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180125133306 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cantiga_milestone_materials (milestoneId INT NOT NULL, materialId INT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, description VARCHAR(255), priority INT DEFAULT NULL, materialType INT DEFAULT 0, INDEX IDX_milestone_id (milestoneId), INDEX IDX_material_id (materialId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cantiga_milestone_materials ADD CONSTRAINT FK_milestone_id FOREIGN KEY (milestoneId) REFERENCES cantiga_milestones (id)');
        $this->addSql('ALTER TABLE cantiga_milestone_materials ADD CONSTRAINT FK_material_id FOREIGN KEY (materialId) REFERENCES cantiga_materials_file (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_milestone_materials DROP FOREIGN KEY FK_milestone_id');
        $this->addSql('ALTER TABLE cantiga_milestone_materials DROP FOREIGN KEY FK_material_id');
        $this->addSql('DROP TABLE cantiga_milestone_materials');

    }
}
