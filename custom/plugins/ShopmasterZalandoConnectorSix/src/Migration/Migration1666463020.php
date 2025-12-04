<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1666463020 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1666463020;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `zalando_price_report` (
              `id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `z_sales_channel_id` varchar(48) NOT NULL,
              `base_regular_price_amount` double NOT NULL,
              `base_regular_price_currency` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
              `base_promotional_price_amount` double DEFAULT NULL,
              `base_promotional_price_currency` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `z_status_id` int(11) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        $sql = "DROP TABLE IF EXISTS `zalando_price_report`;";
        $connection->executeStatement($sql);
    }
}
