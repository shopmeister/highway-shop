<?php declare(strict_types=1);

namespace NetzpShopmanager6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1580205548Statistic extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1580205548;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `s_plugin_netzp_statistics` (
    `id` binary(16) NOT NULL,

    `sales_channel_id` binary(16) DEFAULT NULL,
    `hash` varchar(255),
    `impressions` INT UNSIGNED,

    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
