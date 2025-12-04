<?php declare(strict_types=1);

namespace NetzpShopmanager6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1694420613Index extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612880768;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `s_plugin_netzp_statistics` ADD INDEX `idx.updated_at` (`updated_at`);
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
        //
    }
}
