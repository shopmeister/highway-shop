<?php

namespace ShopmasterZalandoConnectorSix\Core\Content\Zalando\PriceReport;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ZalandoPriceReportDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'zalando_price_report';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ZalandoPriceReportEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ZalandoPriceReportCollection::class;
    }


    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('z_sales_channel_id', 'zSalesChannelId'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('base_regular_price_amount', 'baseRegularPriceAmount'))->addFlags(new ApiAware(), new Required()),
            (new StringField('base_regular_price_currency', 'baseRegularPriceCurrency'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('base_promotional_price_amount', 'basePromotionalPriceAmount'))->addFlags(new ApiAware()),
            (new StringField('base_promotional_price_currency', 'basePromotionalPriceCurrency'))->addFlags(new ApiAware()),
            (new IntField('z_status_id', 'zStatusId'))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}