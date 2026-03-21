<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GitHub configuration fields for CRM projects.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE crm_project ADD github_token VARCHAR(255) DEFAULT NULL, ADD github_repositories JSON NOT NULL DEFAULT ('[]')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_project DROP github_token, DROP github_repositories');
    }
}
