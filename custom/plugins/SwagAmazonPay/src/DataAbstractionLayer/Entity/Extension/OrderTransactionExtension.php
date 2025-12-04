<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\Extension;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\AmazonPay\DataAbstractionLayer\Entity\PaymentNotification\PaymentNotificationDefinition;

class OrderTransactionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderTransactionDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'paymentNotifications',
                PaymentNotificationDefinition::class,
                'order_transaction_id'
            )
        );
    }
}
