<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171220161751 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_edk_faq_question DROP FOREIGN KEY FK_291DC69512469DE2');
        $this->addSql('DROP INDEX idx_291dc69512469de2 ON cantiga_edk_faq_question');
        $this->addSql('RENAME TABLE cantiga_edk_faq_category TO cantiga_faq_category');
        $this->addSql('RENAME TABLE cantiga_edk_faq_question TO cantiga_faq_question');
        $this->addSql('CREATE INDEX IDX_E9E13CD612469DE2 ON cantiga_faq_question (category_id)');
        $this->addSql('ALTER TABLE cantiga_faq_question ADD CONSTRAINT FK_291DC69512469DE2 FOREIGN KEY (category_id) REFERENCES cantiga_faq_category (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cantiga_faq_question DROP FOREIGN KEY FK_E9E13CD612469DE2');
        $this->addSql('DROP INDEX idx_e9e13cd612469de2 ON cantiga_faq_question');
        $this->addSql('RENAME TABLE cantiga_faq_question TO cantiga_edk_faq_question');
        $this->addSql('RENAME TABLE cantiga_faq_category TO cantiga_edk_faq_category');
        $this->addSql('CREATE INDEX IDX_291DC69512469DE2 ON cantiga_edk_faq_question (category_id)');
        $this->addSql('ALTER TABLE cantiga_edk_faq_question ADD CONSTRAINT FK_E9E13CD612469DE2 FOREIGN KEY (category_id) REFERENCES cantiga_edk_faq_category (id)');
    }
}
