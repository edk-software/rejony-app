<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120123755 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cantiga_edk_faq_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cantiga_edk_faq_question (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, topic VARCHAR(255) NOT NULL, answer LONGTEXT NOT NULL, level INT NOT NULL, INDEX IDX_291DC69512469DE2 (category_id), INDEX Q_INDEX_1 (level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cantiga_edk_faq_question ADD CONSTRAINT FK_291DC69512469DE2 FOREIGN KEY (category_id) REFERENCES cantiga_edk_faq_category (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_edk_faq_question DROP FOREIGN KEY FK_291DC69512469DE2');
        $this->addSql('DROP TABLE cantiga_edk_faq_category');
        $this->addSql('DROP TABLE cantiga_edk_faq_question');
    }
}
