<?php declare(strict_types=1);

namespace NetzpShopmanager6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1612880768Index extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612880768;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `order` ADD INDEX `idx.order_date_time` (`order_date_time`);
            ALTER TABLE `order` ADD INDEX `idx.order_date` (`order_date`);
SQL;
        try {
            $connection->executeStatement($sql);
        }
        catch (\Exception) {
            //
        }

        $sql = <<<SQL
            ALTER TABLE `customer` ADD INDEX `idx.created_at` (`created_at`);
SQL;
        try {
            $connection->executeStatement($sql);
        }
        catch (\Exception) {
            //
        }

        $sql = <<<SQL
            ALTER TABLE `s_plugin_netzp_statistics` ADD INDEX `idx.sales_channel_id` (`sales_channel_id`);
            ALTER TABLE `s_plugin_netzp_statistics` ADD INDEX `idx.created_at` (`created_at`);
SQL;
        try {
            $connection->executeStatement($sql);
        }
        catch (\Exception) {
            //
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
