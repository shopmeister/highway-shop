<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1576854000HideSoldoutProducts extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1576854000;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            CREATE TABLE IF NOT EXISTS swkweb_hide_soldout_products_product_availability (
                product_id BINARY(16) NOT NULL,
                product_version_id BINARY(16) NOT NULL,
                sales_channel_id BINARY(16) NOT NULL,
                soldout TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (product_id, product_version_id, sales_channel_id),
                CONSTRAINT `fk.product_availability.product_id__product_version_id` FOREIGN KEY (product_id, product_version_id) REFERENCES product (id, version_id)  ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.product_availability.sales_channel_id` FOREIGN KEY (sales_channel_id) REFERENCES sales_channel (id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void {}
}
