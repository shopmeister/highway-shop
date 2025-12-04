<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1624275180IpnRemoveTransactionId extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1624275180;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement('ALTER TABLE `swag_amazon_pay_payment_notification` DROP FOREIGN KEY `fk.swag_amazon_pay_payment_notification.transaction_id`');
        } catch (\Exception $e) {
            // do nothing
        }
        try {
            $connection->executeStatement('ALTER TABLE `swag_amazon_pay_payment_notification` DROP COLUMN `transaction_id`');
        } catch (\Exception $e) {
            // do nothing
        }

    }
}
