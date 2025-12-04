<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1589963718AddPaymentNotification extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589963718;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `swag_amazon_pay_payment_notification` (
                `id` binary(16) NOT NULL,
                `transaction_id` BINARY(16) NULL,
                `object_id` VARCHAR(32) NOT NULL,
                `object_type` VARCHAR(16) NOT NULL,
                `charge_permission_id` VARCHAR(64) NULL,
                `notification_id` VARCHAR(64) NOT NULL,
                `notification_version` VARCHAR(8) NOT NULL,
                `notification_type` VARCHAR(32) NOT NULL,
                `processed` INT(1) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,

                PRIMARY KEY (`id`),
                KEY `fk.swag_amazon_pay_payment_notification.transaction_id` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }
}
