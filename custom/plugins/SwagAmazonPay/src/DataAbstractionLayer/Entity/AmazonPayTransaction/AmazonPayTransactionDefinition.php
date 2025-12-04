<?php

declare(strict_types=1);

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AmazonPayTransactionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_amazon_pay_transaction';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return AmazonPayTransactionCollection::class;
    }

    public function getEntityClass(): string
    {
        return AmazonPayTransactionEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField('order_transaction_id', 'orderTransactionId', OrderTransactionDefinition::class),
            new FkField('order_id', 'orderId', OrderDefinition::class),
            new FkField('parent_id', 'parentId', AmazonPayTransactionDefinition::class),

            (new StringField('merchant_id', 'merchantId')),
            (new StringField('reference', 'reference')),
            (new StringField('mode', 'mode')),
            (new StringField('type', 'type')),
            (new DateTimeField('time', 'time')),
            (new DateTimeField('expiration', 'expiration')),
            (new FloatField('amount', 'amount')),
            (new FloatField('captured_amount', 'capturedAmount')),
            (new FloatField('refunded_amount', 'refundedAmount')),
            (new StringField('currency', 'currency')),
            (new StringField('status', 'status')),
            (new BoolField('customer_informed', 'customerInformed')),
            (new BoolField('admin_informed', 'adminInformed')),

            new ManyToOneAssociationField('orderTransaction', 'order_transaction_id', OrderTransactionDefinition::class, 'id', true),
            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', true),
            new ManyToOneAssociationField('parent', 'parent_id', AmazonPayTransactionDefinition::class, 'id', true),
        ]);
    }
}
