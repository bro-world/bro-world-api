<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use RuntimeException;

final class Version20260322143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Attach CRM task requests to a CRM repository and add repository/status index.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE crm_task_request ADD repository_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)'");

        $this->addSql(
            'UPDATE crm_task_request task_request
            INNER JOIN crm_task task ON task_request.task_id = task.id
            INNER JOIN crm_project project ON task.project_id = project.id
            SET task_request.repository_id = (
                SELECT repository.id
                FROM crm_repository repository
                WHERE repository.project_id = project.id
                ORDER BY repository.created_at ASC, repository.id ASC
                LIMIT 1
            )
            WHERE task_request.repository_id IS NULL'
        );

        $nullCount = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM crm_task_request WHERE repository_id IS NULL');
        if (0 !== $nullCount) {
            throw new RuntimeException('Cannot migrate crm_task_request.repository_id to NOT NULL: at least one task request has no repository candidate.');
        }

        $this->addSql("ALTER TABLE crm_task_request CHANGE repository_id repository_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)'");
        $this->addSql('ALTER TABLE crm_task_request ADD CONSTRAINT FK_CRM_TASK_REQUEST_REPOSITORY FOREIGN KEY (repository_id) REFERENCES crm_repository (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_crm_task_request_repository_status ON crm_task_request (repository_id, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_task_request DROP FOREIGN KEY FK_CRM_TASK_REQUEST_REPOSITORY');
        $this->addSql('DROP INDEX idx_crm_task_request_repository_status ON crm_task_request');
        $this->addSql('ALTER TABLE crm_task_request DROP repository_id');
    }
}
