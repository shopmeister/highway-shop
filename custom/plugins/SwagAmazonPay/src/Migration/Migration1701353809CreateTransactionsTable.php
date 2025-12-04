<?php declare(strict_types=1);

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1701353809CreateTransactionsTable extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1701353809;
    }

    public function update(Connection $connection): void
    {

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `swag_amazon_pay_transaction` (
              `id` binary(16) NOT NULL,
              `parent_id` BINARY(16) DEFAULT NULL,
              `order_id` BINARY(16) DEFAULT NULL,
              `order_transaction_id` BINARY(16) DEFAULT NULL,
              `merchant_id` VARCHAR(32) DEFAULT NULL,
              `reference` VARCHAR(128) DEFAULT NULL,
              `mode` VARCHAR(16) DEFAULT NULL,
              `type` VARCHAR(16) DEFAULT NULL,
              `time` datetime DEFAULT NULL,
              `expiration` datetime DEFAULT NULL,
              `amount` double DEFAULT NULL,
              `captured_amount` double DEFAULT NULL,
              `refunded_amount` double DEFAULT NULL,
              `currency` VARCHAR(3) DEFAULT NULL,
              `status` VARCHAR(32) DEFAULT NULL,
              `customer_informed` tinyint(1) DEFAULT NULL,
              `admin_informed` tinyint(1) DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `swag_amazon_pay_transaction_order_id` (`order_id`),
              KEY `swag_amazon_pay_transaction_parent_id` (`parent_id`),
              KEY `swag_amazon_pay_transaction_order_transaction_id` (`order_transaction_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }
}
