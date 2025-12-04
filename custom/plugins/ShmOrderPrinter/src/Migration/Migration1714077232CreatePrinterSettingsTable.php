<?php

declare(strict_types=1);

namespace Shm\OrderPrinter\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1714077232CreatePrinterSettingsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1714077232;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `shm_printer_settings` (
                `id` BINARY(16) NOT NULL,
                `setting` JSON NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `shm_printer_settings`');
    }
}