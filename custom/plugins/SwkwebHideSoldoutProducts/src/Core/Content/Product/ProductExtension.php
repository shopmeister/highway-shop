<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swkweb\HideSoldoutProducts\Core\Content\Product\Aggregate\ProductAvailability\ProductAvailabilityDefinition;

class ProductExtension extends EntityExtension
{
    final public const AVAILABILITY_FIELD = 'swkwebHideSoldoutProductsAvailability';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(self::AVAILABILITY_FIELD, ProductAvailabilityDefinition::class, 'product_id'))
                ->addFlags(new CascadeDelete()),
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
