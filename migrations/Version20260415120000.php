<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add coins_amount to shop_product to store delivered virtual currency quantity per product.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_product ADD coins_amount INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_product DROP coins_amount');
    }
}
