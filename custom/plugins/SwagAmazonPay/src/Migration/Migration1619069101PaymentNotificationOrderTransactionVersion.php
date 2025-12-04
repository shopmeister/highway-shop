<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1619069101PaymentNotificationOrderTransactionVersion extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1619069101;
    }

    public function update(Connection $connection): void
    {
        $this->cleanUpTable($connection);
        try {
            $connection->executeStatement(
                'ALTER TABLE `swag_amazon_pay_payment_notification` ADD COLUMN `order_transaction_id` BINARY(16) NOT NULL AFTER `transaction_id`;'
            );
        } catch (\Exception $e) {
            // do nothing
        }

        try {
            $connection->executeStatement(
                'ALTER TABLE `swag_amazon_pay_payment_notification` ADD COLUMN `order_transaction_version_id` BINARY(16) NOT NULL AFTER `order_transaction_id`;'
            );
        } catch (\Exception $e) {
            // do nothing
        }
        $this->setOrderTransactionIds($connection);
        try {
            $connection->executeStatement(
                'ALTER TABLE `swag_amazon_pay_payment_notification` ADD CONSTRAINT `fk.swag_amazon_pay_payment_notification.order_transaction_id` FOREIGN KEY (`order_transaction_id`, `order_transaction_version_id`) REFERENCES `order_transaction` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE;'
            );
        } catch (\Exception $e) {
            // do nothing
        }
    }

    private function setOrderTransactionIds(Connection $connection): void
    {
        try {
            $result = $connection->executeQuery('SELECT `id`, `transaction_id` as `transactionId` FROM `swag_amazon_pay_payment_notification`;');

            $transactionIdIdMapping = $result->fetchAllAssociative();
            foreach ($transactionIdIdMapping as $mapping) {
                if (!\array_key_exists('id', $mapping) || !\array_key_exists('transactionId', $mapping)) {
                    continue;
                }

                $connection->update(
                    'swag_amazon_pay_payment_notification',
                    [
                        'order_transaction_id' => $mapping['transactionId'],
                        'order_transaction_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
                    ],
                    [
                        'id' => $mapping['id'],
                    ]
                );
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    private function cleanUpTable(Connection $connection): void
    {
        $connection->executeStatement(
            'DELETE FROM `swag_amazon_pay_payment_notification` WHERE `transaction_id` IS NULL;'
        );
    }
}
