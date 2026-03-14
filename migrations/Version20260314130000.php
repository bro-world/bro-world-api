<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recruit interviews entity and table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE recruit_interview (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)', application_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)', scheduled_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', duration_minutes INT NOT NULL, mode VARCHAR(25) NOT NULL, location_or_url VARCHAR(255) NOT NULL, interviewer_ids JSON NOT NULL, status VARCHAR(25) NOT NULL DEFAULT 'planned', notes LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_recruit_interview_application_scheduled_at (application_id, scheduled_at), INDEX IDX_B44E3A4A57698A6A (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE recruit_interview ADD CONSTRAINT FK_B44E3A4A57698A6A FOREIGN KEY (application_id) REFERENCES recruit_application (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recruit_interview DROP FOREIGN KEY FK_B44E3A4A57698A6A');
        $this->addSql('DROP TABLE recruit_interview');
    }
}
