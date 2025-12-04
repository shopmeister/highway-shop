<?php declare(strict_types=1);


namespace Redgecko\Magnalister\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * For each new package of magnalister plugin this script will be generated with a new time stamp (via magnalisterSetup)
 * It is important to check if "magnalister_shopware6" is not deleted manually.
 * And to run after Update-Script of magnalister "after-update" should be removed from "magnalister_config"
 *
 * Class Migration1554708925Bundle
 * @package Redgecko\Magnalister\Migration
 */
class Migration1554708925Bundle extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1554708925;
    }

    public function update(Connection $connection): void
    {
        /*
         * Data in magnalister_shopware6 table is not important to be keeped for a long time,
         * they can be removed by each update of Plugin
         */
        $sDropQuery = '
            DROP TABLE IF EXISTS `magnalister_shopware6`';
        try {
            $connection->executeQuery($sDropQuery);
        } catch (\Throwable $th) {
            $connection->executeUpdate($sDropQuery);
        }
        $sCreateQuery = '
            CREATE TABLE IF NOT EXISTS `magnalister_shopware6` (
            `id` BINARY(16) NOT NULL,
            `key` VARCHAR(255) NOT NULL,
            `ip` VARCHAR(255) NULL,
            `browser` VARCHAR(255) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';
        try {
            $connection->executeQuery($sCreateQuery);
        } catch (\Throwable $th) {
            if (method_exists($connection, 'executeUpdate')) {
                $connection->executeUpdate($sCreateQuery);
            } else {
                $connection->executeStatement($sCreateQuery);
            }
        }
        if (method_exists($connection, 'fetchAll')) {
            $aConfigTableExist = $connection->fetchAll("SHOW TABLES LIKE 'magnalister_config'");
        } else {
            $aConfigTableExist = $connection->fetchAllAssociative("SHOW TABLES LIKE 'magnalister_config'");
        }
        if(!empty($aConfigTableExist)){
            $connection->delete('magnalister_config', array(
                'mpid' => '0',
                'mkey' => 'after-update',
            ));
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}