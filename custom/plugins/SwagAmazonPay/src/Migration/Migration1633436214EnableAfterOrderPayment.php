<?php declare(strict_types=1);

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Components\PaymentHandler\AmazonPaymentHandler;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1633436214EnableAfterOrderPayment extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1633436214;
    }

    public function update(Connection $connection): void
    {
        $connection->update(
            'payment_method',
            [
                'after_order_enabled' => 1,
            ],
            [
                'handler_identifier' => AmazonPaymentHandler::class,
            ]
        );
    }
}
