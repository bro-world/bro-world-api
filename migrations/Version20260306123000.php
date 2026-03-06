<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Add configuration table.
 */
final class Version20260306123000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add configuration table with unique configuration_key, JSON value, scope enum and private encryption params.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        return false;
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql("CREATE TABLE configuration (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)', configuration_key VARCHAR(255) NOT NULL, configuration_value JSON NOT NULL, scope VARCHAR(50) NOT NULL, private TINYINT(1) NOT NULL DEFAULT 0, configuration_value_parameters JSON DEFAULT NULL COMMENT 'Configuration value decrypt parameters when encrypted', created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', UNIQUE INDEX uq_configuration_key (configuration_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('DROP TABLE configuration');
    }
}
