<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260309110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create chat_message and chat_message_reaction tables.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('CREATE TABLE chat_message (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', conversation_id BINARY(16) NOT NULL, sender_id BINARY(16) NOT NULL, content LONGTEXT NOT NULL, read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', attachments JSON NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_chat_message_conversation_id (conversation_id), INDEX idx_chat_message_created_at (created_at), INDEX idx_chat_message_sender_id (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message_reaction (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', message_id BINARY(16) NOT NULL, user_id BINARY(16) NOT NULL, reaction VARCHAR(32) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_chat_message_reaction_message_id (message_id), INDEX idx_chat_message_reaction_user_id (user_id), UNIQUE INDEX uq_chat_message_reaction_message_user_type (message_id, user_id, reaction), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_6EEC5A89C1A0C444 FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_6EEC5A898E7927C9 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message_reaction ADD CONSTRAINT FK_45E6B164537A1329 FOREIGN KEY (message_id) REFERENCES chat_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message_reaction ADD CONSTRAINT FK_45E6B164A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE chat_message_reaction DROP FOREIGN KEY FK_45E6B164537A1329');
        $this->addSql('ALTER TABLE chat_message_reaction DROP FOREIGN KEY FK_45E6B164A76ED395');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_6EEC5A89C1A0C444');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_6EEC5A898E7927C9');
        $this->addSql('DROP TABLE chat_message_reaction');
        $this->addSql('DROP TABLE chat_message');
    }
}
