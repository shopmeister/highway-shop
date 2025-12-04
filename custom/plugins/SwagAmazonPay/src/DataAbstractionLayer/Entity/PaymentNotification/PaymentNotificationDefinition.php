<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\PaymentNotification;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PaymentNotificationDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_amazon_pay_payment_notification';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PaymentNotificationCollection::class;
    }

    public function getEntityClass(): string
    {
        return PaymentNotificationEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField('order_transaction_id', 'orderTransactionId', OrderTransactionDefinition::class),
            (new ReferenceVersionField(OrderTransactionDefinition::class, 'order_transaction_version_id'))->addFlags(new Required()),

            (new StringField('object_type', 'objectType'))->addFlags(new Required()),
            (new StringField('object_id', 'objectId'))->addFlags(new Required()),
            new StringField('charge_permission_id', 'chargePermissionId'),
            (new StringField('notification_id', 'notificationId'))->addFlags(new Required()),
            (new StringField('notification_version', 'notificationVersion'))->addFlags(new Required()),
            (new StringField('notification_type', 'notificationType'))->addFlags(new Required()),
            new BoolField('processed', 'processed'),

            new ManyToOneAssociationField('orderTransaction', 'order_transaction_id', OrderTransactionDefinition::class, 'id', false),
        ]);
    }
}
