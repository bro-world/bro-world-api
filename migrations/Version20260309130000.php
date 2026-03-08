<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260309130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add extended metadata fields to calendar_event.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql("ALTER TABLE calendar_event ADD location VARCHAR(255) DEFAULT NULL, ADD is_all_day TINYINT(1) NOT NULL DEFAULT 0, ADD timezone VARCHAR(64) DEFAULT NULL, ADD is_cancelled TINYINT(1) NOT NULL DEFAULT 0, ADD url VARCHAR(2048) DEFAULT NULL, ADD color VARCHAR(32) DEFAULT NULL, ADD background_color VARCHAR(32) DEFAULT NULL, ADD border_color VARCHAR(32) DEFAULT NULL, ADD text_color VARCHAR(32) DEFAULT NULL, ADD organizer_name VARCHAR(255) DEFAULT NULL, ADD organizer_email VARCHAR(255) DEFAULT NULL, ADD attendees JSON DEFAULT NULL, ADD rrule LONGTEXT DEFAULT NULL, ADD recurrence_exceptions JSON DEFAULT NULL, ADD recurrence_end_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD recurrence_count INT DEFAULT NULL, ADD reminders JSON DEFAULT NULL, ADD metadata JSON DEFAULT NULL");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE calendar_event DROP location, DROP is_all_day, DROP timezone, DROP is_cancelled, DROP url, DROP color, DROP background_color, DROP border_color, DROP text_color, DROP organizer_name, DROP organizer_email, DROP attendees, DROP rrule, DROP recurrence_exceptions, DROP recurrence_end_at, DROP recurrence_count, DROP reminders, DROP metadata');
    }
}
