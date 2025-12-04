<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1623820553IpnOrderTransactionIdNullable extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1623820553;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `swag_amazon_pay_payment_notification` MODIFY `order_transaction_id` BINARY(16) NULL');
    }
}
