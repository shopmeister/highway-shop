<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1623656945SignUpToken extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1623656945;
    }

    public function update(Connection $connection): void
    {
        $createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS `swag_amazon_pay_signup_token` (
    `id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($createTableSql);
    }
}
