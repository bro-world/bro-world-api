<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260309120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Rename core domain tables to explicit module-prefixed names and recreate impacted foreign keys.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE platform_application DROP FOREIGN KEY FK_PLATFORM_APPLICATION_PLATFORM_ID');
        $this->addSql('ALTER TABLE plugin DROP FOREIGN KEY FK_PLUGIN_PLATFORM_ID');
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_CONFIGURATION_PLATFORM_ID');
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_CONFIGURATION_PLUGIN_ID');
        $this->addSql('ALTER TABLE platform_application_plugin DROP FOREIGN KEY FK_PLATFORM_APPLICATION_PLUGIN_PLUGIN_ID');
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_E72A1268A40BC2D5');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_C7A1A4B28E7927C9');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_6EEC5A898E7927C9');

        $this->addSql('RENAME TABLE configuration TO configuration_configuration');
        $this->addSql('RENAME TABLE conversation TO chat_conversation');
        $this->addSql('RENAME TABLE conversation_participant TO chat_conversation_participant');
        $this->addSql('RENAME TABLE calendar TO calendar_calendar');
        $this->addSql('RENAME TABLE platform TO platform_platform');
        $this->addSql('RENAME TABLE plugin TO platform_plugin');

        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX idx_conversation_chat_id TO idx_chat_conversation_chat_id');
        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX idx_conversation_application_slug TO idx_chat_conversation_application_slug');
        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX uq_conversation_chat_application_slug TO uq_chat_conversation_chat_application_slug');

        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX idx_conversation_participant_conversation_id TO idx_chat_conversation_participant_conversation_id');
        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX idx_conversation_participant_user_id TO idx_chat_conversation_participant_user_id');
        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX uq_conversation_participant_conversation_user TO uq_chat_conversation_participant_conversation_user');

        $this->addSql('ALTER TABLE platform_platform RENAME INDEX idx_platform_user_id TO idx_platform_platform_user_id');
        $this->addSql('ALTER TABLE platform_plugin RENAME INDEX idx_plugin_platform_id TO idx_platform_plugin_platform_id');

        $this->addSql('ALTER TABLE platform_application ADD CONSTRAINT FK_PLATFORM_APPLICATION_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform_platform (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE platform_plugin ADD CONSTRAINT FK_PLUGIN_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform_platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE configuration_configuration ADD CONSTRAINT FK_CONFIGURATION_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform_platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE configuration_configuration ADD CONSTRAINT FK_CONFIGURATION_PLUGIN_ID FOREIGN KEY (plugin_id) REFERENCES platform_plugin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE platform_application_plugin ADD CONSTRAINT FK_PLATFORM_APPLICATION_PLUGIN_PLUGIN_ID FOREIGN KEY (plugin_id) REFERENCES platform_plugin (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_E72A1268A40BC2D5 FOREIGN KEY (calendar_id) REFERENCES calendar_calendar (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE chat_conversation_participant ADD CONSTRAINT FK_C7A1A4B28E7927C9 FOREIGN KEY (conversation_id) REFERENCES chat_conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_6EEC5A898E7927C9 FOREIGN KEY (conversation_id) REFERENCES chat_conversation (id) ON DELETE CASCADE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE platform_application DROP FOREIGN KEY FK_PLATFORM_APPLICATION_PLATFORM_ID');
        $this->addSql('ALTER TABLE platform_plugin DROP FOREIGN KEY FK_PLUGIN_PLATFORM_ID');
        $this->addSql('ALTER TABLE configuration_configuration DROP FOREIGN KEY FK_CONFIGURATION_PLATFORM_ID');
        $this->addSql('ALTER TABLE configuration_configuration DROP FOREIGN KEY FK_CONFIGURATION_PLUGIN_ID');
        $this->addSql('ALTER TABLE platform_application_plugin DROP FOREIGN KEY FK_PLATFORM_APPLICATION_PLUGIN_PLUGIN_ID');
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_E72A1268A40BC2D5');
        $this->addSql('ALTER TABLE chat_conversation_participant DROP FOREIGN KEY FK_C7A1A4B28E7927C9');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_6EEC5A898E7927C9');

        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX idx_chat_conversation_participant_conversation_id TO idx_conversation_participant_conversation_id');
        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX idx_chat_conversation_participant_user_id TO idx_conversation_participant_user_id');
        $this->addSql('ALTER TABLE chat_conversation_participant RENAME INDEX uq_chat_conversation_participant_conversation_user TO uq_conversation_participant_conversation_user');

        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX idx_chat_conversation_chat_id TO idx_conversation_chat_id');
        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX idx_chat_conversation_application_slug TO idx_conversation_application_slug');
        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX uq_chat_conversation_chat_application_slug TO uq_conversation_chat_application_slug');

        $this->addSql('ALTER TABLE platform_platform RENAME INDEX idx_platform_platform_user_id TO idx_platform_user_id');
        $this->addSql('ALTER TABLE platform_plugin RENAME INDEX idx_platform_plugin_platform_id TO idx_plugin_platform_id');

        $this->addSql('RENAME TABLE configuration_configuration TO configuration');
        $this->addSql('RENAME TABLE chat_conversation TO conversation');
        $this->addSql('RENAME TABLE chat_conversation_participant TO conversation_participant');
        $this->addSql('RENAME TABLE calendar_calendar TO calendar');
        $this->addSql('RENAME TABLE platform_platform TO platform');
        $this->addSql('RENAME TABLE platform_plugin TO plugin');

        $this->addSql('ALTER TABLE platform_application ADD CONSTRAINT FK_PLATFORM_APPLICATION_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE plugin ADD CONSTRAINT FK_PLUGIN_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_CONFIGURATION_PLATFORM_ID FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_CONFIGURATION_PLUGIN_ID FOREIGN KEY (plugin_id) REFERENCES plugin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE platform_application_plugin ADD CONSTRAINT FK_PLATFORM_APPLICATION_PLUGIN_PLUGIN_ID FOREIGN KEY (plugin_id) REFERENCES plugin (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_E72A1268A40BC2D5 FOREIGN KEY (calendar_id) REFERENCES calendar (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_C7A1A4B28E7927C9 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_6EEC5A898E7927C9 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
    }
}
