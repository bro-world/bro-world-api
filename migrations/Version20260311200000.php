<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add enum-like columns for school_exam type/status/term';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE school_exam ADD type VARCHAR(32) NOT NULL DEFAULT 'QUIZ', ADD status VARCHAR(32) NOT NULL DEFAULT 'DRAFT', ADD term VARCHAR(32) NOT NULL DEFAULT 'TERM_1'");
        $this->addSql("UPDATE school_exam SET type = 'QUIZ' WHERE type = '' OR type IS NULL");
        $this->addSql("UPDATE school_exam SET status = 'DRAFT' WHERE status = '' OR status IS NULL");
        $this->addSql("UPDATE school_exam SET term = 'TERM_1' WHERE term = '' OR term IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE school_exam DROP type, DROP status, DROP term');
    }
}
