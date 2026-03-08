<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260309143000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Remove application_slug from chat_conversation and key conversations by chat_id only.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE chat_conversation DROP INDEX uq_chat_conversation_chat_application_slug');
        $this->addSql('ALTER TABLE chat_conversation DROP INDEX idx_chat_conversation_application_slug');
        $this->addSql('ALTER TABLE chat_conversation DROP COLUMN application_slug');
        $this->addSql('CREATE UNIQUE INDEX uq_conversation_chat_id ON chat_conversation (chat_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE chat_conversation ADD application_slug VARCHAR(100) NOT NULL');
        $this->addSql('CREATE INDEX idx_chat_conversation_application_slug ON chat_conversation (application_slug)');
        $this->addSql('CREATE UNIQUE INDEX uq_chat_conversation_chat_application_slug ON chat_conversation (chat_id, application_slug)');
        $this->addSql('DROP INDEX uq_conversation_chat_id ON chat_conversation');
    }
}
