<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor shop module with new metadata fields and enums for products, categories, shops and tags';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE shop ADD description LONGTEXT DEFAULT NULL, ADD is_active TINYINT(1) DEFAULT 1 NOT NULL");

        $this->addSql("ALTER TABLE shop_category ADD slug VARCHAR(120) DEFAULT '' NOT NULL, ADD description LONGTEXT DEFAULT NULL");
        $this->addSql("UPDATE shop_category SET slug = LOWER(REPLACE(name, ' ', '-')) WHERE slug = ''");

        $this->addSql("ALTER TABLE shop_tag ADD type VARCHAR(30) DEFAULT 'marketing' NOT NULL");

        $this->addSql("ALTER TABLE shop_product ADD sku VARCHAR(64) DEFAULT '' NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD currency_code VARCHAR(3) DEFAULT 'EUR' NOT NULL, ADD stock INT DEFAULT 0 NOT NULL, ADD is_featured TINYINT(1) DEFAULT 0 NOT NULL, ADD status VARCHAR(30) DEFAULT 'draft' NOT NULL");
        $this->addSql("UPDATE shop_product SET sku = UPPER(SUBSTRING(REPLACE(REPLACE(id, '-', ''), ' ', ''), 1, 12)) WHERE sku = ''");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SHOP_PRODUCT_SKU ON shop_product (sku)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_SHOP_PRODUCT_SKU ON shop_product');
        $this->addSql('ALTER TABLE shop_product DROP sku, DROP description, DROP currency_code, DROP stock, DROP is_featured, DROP status');

        $this->addSql('ALTER TABLE shop_tag DROP type');

        $this->addSql('ALTER TABLE shop_category DROP slug, DROP description');

        $this->addSql('ALTER TABLE shop DROP description, DROP is_active');
    }
}
