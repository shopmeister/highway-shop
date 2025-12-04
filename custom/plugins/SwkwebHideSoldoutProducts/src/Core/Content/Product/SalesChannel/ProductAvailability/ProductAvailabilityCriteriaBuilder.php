<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\SalesChannel\ProductAvailability;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class ProductAvailabilityCriteriaBuilder
{
    public function __construct(private readonly bool $elasticsearchEnabled) {}

    public function addNotSoldoutFilter(Criteria $criteria, string $salesChannelId): void
    {
        if ($criteria->hasState(self::class)) {
            return;
        }

        $criteria->addFilter($this->getNotSoldoutFilter($salesChannelId));
        $criteria->addState(self::class);
    }

    public function getNotSoldoutFilter(string $salesChannelId): Filter
    {
        $soldoutFilter = new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('product.swkwebHideSoldoutProductsAvailability.salesChannelId', $salesChannelId),
            new EqualsFilter('product.swkwebHideSoldoutProductsAvailability.soldout', false),
        ]);

        if ($this->elasticsearchEnabled) {
            return $soldoutFilter;
        }

        return new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('product.swkwebHideSoldoutProductsAvailability.productId', null),
            $soldoutFilter,
        ]);
    }
}
