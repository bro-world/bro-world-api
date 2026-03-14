<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CRM employee, contact and billing tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE crm_employee (id BINARY(16) NOT NULL COMMENT "(DC2Type:uuid_binary_ordered_time)", crm_id BINARY(16) NOT NULL, first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, email VARCHAR(255) DEFAULT NULL, position_name VARCHAR(120) DEFAULT NULL, role_name VARCHAR(120) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", updated_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", INDEX IDX_CRM_EMPLOYEE_CRM (crm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_contact (id BINARY(16) NOT NULL COMMENT "(DC2Type:uuid_binary_ordered_time)", crm_id BINARY(16) NOT NULL, company_id BINARY(16) DEFAULT NULL, first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(60) DEFAULT NULL, job_title VARCHAR(120) DEFAULT NULL, city VARCHAR(120) DEFAULT NULL, score INT NOT NULL DEFAULT 0, created_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", updated_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", INDEX IDX_CRM_CONTACT_CRM (crm_id), INDEX IDX_CRM_CONTACT_COMPANY (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_billing (id BINARY(16) NOT NULL COMMENT "(DC2Type:uuid_binary_ordered_time)", company_id BINARY(16) NOT NULL, label VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, currency VARCHAR(3) NOT NULL DEFAULT "EUR", status VARCHAR(30) NOT NULL DEFAULT "pending", due_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", paid_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", created_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", updated_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", INDEX IDX_CRM_BILLING_COMPANY (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE crm_employee ADD CONSTRAINT FK_CRM_EMPLOYEE_CRM FOREIGN KEY (crm_id) REFERENCES crm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crm_contact ADD CONSTRAINT FK_CRM_CONTACT_CRM FOREIGN KEY (crm_id) REFERENCES crm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crm_contact ADD CONSTRAINT FK_CRM_CONTACT_COMPANY FOREIGN KEY (company_id) REFERENCES crm_company (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE crm_billing ADD CONSTRAINT FK_CRM_BILLING_COMPANY FOREIGN KEY (company_id) REFERENCES crm_company (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_billing DROP FOREIGN KEY FK_CRM_BILLING_COMPANY');
        $this->addSql('ALTER TABLE crm_contact DROP FOREIGN KEY FK_CRM_CONTACT_CRM');
        $this->addSql('ALTER TABLE crm_contact DROP FOREIGN KEY FK_CRM_CONTACT_COMPANY');
        $this->addSql('ALTER TABLE crm_employee DROP FOREIGN KEY FK_CRM_EMPLOYEE_CRM');

        $this->addSql('DROP TABLE crm_billing');
        $this->addSql('DROP TABLE crm_contact');
        $this->addSql('DROP TABLE crm_employee');
    }
}
