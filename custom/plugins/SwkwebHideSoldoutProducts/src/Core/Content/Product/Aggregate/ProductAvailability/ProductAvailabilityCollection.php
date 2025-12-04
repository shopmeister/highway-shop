<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\Aggregate\ProductAvailability;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProductAvailabilityEntity>
 */
class ProductAvailabilityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductAvailabilityEntity::class;
    }
}
