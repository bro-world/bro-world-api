<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260322120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move CRM project GitHub repositories from JSON column to crm_repository relation table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE crm_repository (id BINARY(16) NOT NULL COMMENT "(DC2Type:uuid_binary_ordered_time)", project_id BINARY(16) NOT NULL COMMENT "(DC2Type:uuid_binary_ordered_time)", provider VARCHAR(30) NOT NULL DEFAULT "github", owner VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, default_branch VARCHAR(255) DEFAULT NULL, is_private TINYINT(1) NOT NULL DEFAULT 0, html_url VARCHAR(1024) DEFAULT NULL, external_id BIGINT DEFAULT NULL, last_synced_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", sync_status VARCHAR(40) NOT NULL DEFAULT "pending", payload JSON DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", updated_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)", INDEX IDX_CRM_REPOSITORY_PROJECT (project_id), INDEX idx_crm_repository_provider (provider), INDEX idx_crm_repository_external_id (external_id), UNIQUE INDEX uq_crm_repository_project_provider_full_name (project_id, provider, full_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crm_repository ADD CONSTRAINT FK_CRM_REPOSITORY_PROJECT FOREIGN KEY (project_id) REFERENCES crm_project (id) ON DELETE CASCADE');

        $this->addSql(<<<'SQL'
            INSERT INTO crm_repository (
                id,
                project_id,
                provider,
                owner,
                name,
                full_name,
                default_branch,
                is_private,
                html_url,
                external_id,
                last_synced_at,
                sync_status,
                payload,
                created_at,
                updated_at
            )
            SELECT
                UUID_TO_BIN(UUID(), 1),
                project.id,
                'github',
                SUBSTRING_INDEX(repository.full_name, '/', 1),
                SUBSTRING_INDEX(repository.full_name, '/', -1),
                repository.full_name,
                NULLIF(repository.default_branch, ''),
                0,
                NULL,
                NULL,
                NULL,
                'pending',
                repository.payload,
                project.created_at,
                project.updated_at
            FROM crm_project project
            INNER JOIN JSON_TABLE(
                project.github_repositories,
                '$[*]' COLUMNS (
                    full_name VARCHAR(255) PATH '$.fullName',
                    default_branch VARCHAR(255) PATH '$.defaultBranch' DEFAULT NULL ON EMPTY,
                    payload JSON PATH '$'
                )
            ) repository
            WHERE JSON_VALID(project.github_repositories)
              AND repository.full_name IS NOT NULL
              AND repository.full_name <> ''
        SQL);

        $this->addSql('ALTER TABLE crm_project DROP github_repositories');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE crm_project ADD github_repositories JSON NOT NULL DEFAULT ('[]')");
        $this->addSql(<<<'SQL'
            UPDATE crm_project project
            LEFT JOIN (
                SELECT
                    repository.project_id,
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'fullName', repository.full_name,
                            'defaultBranch', repository.default_branch
                        )
                    ) AS repositories
                FROM crm_repository repository
                GROUP BY repository.project_id
            ) migrated ON migrated.project_id = project.id
            SET project.github_repositories = COALESCE(migrated.repositories, JSON_ARRAY())
        SQL);

        $this->addSql('ALTER TABLE crm_repository DROP FOREIGN KEY FK_CRM_REPOSITORY_PROJECT');
        $this->addSql('DROP TABLE crm_repository');
    }
}
