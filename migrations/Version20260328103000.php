<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Google Calendar linkage columns to calendar_event.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar_event ADD google_event_id VARCHAR(255) DEFAULT NULL, ADD google_calendar_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_calendar_event_google_event_id ON calendar_event (google_event_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_calendar_event_google_event_id ON calendar_event');
        $this->addSql('ALTER TABLE calendar_event DROP google_event_id, DROP google_calendar_id');
    }
}
