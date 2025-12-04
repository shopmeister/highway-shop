<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\System\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Swkweb\HideSoldoutProducts\Core\Content\Product\Aggregate\ProductAvailability\ProductAvailabilityDefinition;

class SalesChannelExtension extends EntityExtension
{
    final public const AVAILABILITIES_FIELD = 'swkwebHideSoldoutProductsAvailabilities';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(self::AVAILABILITIES_FIELD, ProductAvailabilityDefinition::class, 'sales_channel_id'))
                ->addFlags(new CascadeDelete()),
        );
    }

    public function getDefinitionClass(): string
    {
        return SalesChannelDefinition::class;
    }
}
