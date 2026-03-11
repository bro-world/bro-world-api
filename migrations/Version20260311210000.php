<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enrich chat conversation, participant and message schema for typing, read state and soft delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE chat_conversation ADD type VARCHAR(25) NOT NULL DEFAULT 'direct', ADD title VARCHAR(255) DEFAULT NULL, ADD last_message_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD archived_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql('CREATE INDEX idx_conversation_chat_type_last_message_at ON chat_conversation (chat_id, type, last_message_at)');

        $this->addSql("ALTER TABLE chat_conversation_participant ADD last_read_message_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD muted_until DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD role VARCHAR(25) NOT NULL DEFAULT 'member'");
        $this->addSql('CREATE INDEX idx_conversation_participant_conversation_user_last_read ON chat_conversation_participant (conversation_id, user_id, last_read_message_at)');

        $this->addSql("ALTER TABLE chat_message ADD edited_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD metadata JSON DEFAULT NULL");
        $this->addSql('CREATE INDEX idx_chat_message_conversation_created_deleted ON chat_message (conversation_id, created_at, deleted_at)');
        $this->addSql('UPDATE chat_message SET metadata = JSON_ARRAY() WHERE metadata IS NULL');
        $this->addSql('ALTER TABLE chat_message MODIFY metadata JSON NOT NULL');
        $this->addSql('UPDATE chat_conversation SET last_message_at = updated_at WHERE last_message_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_chat_message_conversation_created_deleted ON chat_message');
        $this->addSql('ALTER TABLE chat_message DROP edited_at, DROP deleted_at, DROP metadata');

        $this->addSql('DROP INDEX idx_conversation_participant_conversation_user_last_read ON chat_conversation_participant');
        $this->addSql('ALTER TABLE chat_conversation_participant DROP last_read_message_at, DROP muted_until, DROP role');

        $this->addSql('DROP INDEX idx_conversation_chat_type_last_message_at ON chat_conversation');
        $this->addSql('ALTER TABLE chat_conversation DROP type, DROP title, DROP last_message_at, DROP archived_at');
    }
}
