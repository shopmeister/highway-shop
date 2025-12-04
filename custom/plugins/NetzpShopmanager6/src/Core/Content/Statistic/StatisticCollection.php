<?php declare(strict_types=1);

namespace NetzpShopmanager6\Core\Content\Statistic;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(StatisticEntity $entity)
 * @method void            set(string $key, StatisticEntity $entity)
 * @method StatisticEntity[]    getIterator()
 * @method StatisticEntity[]    getElements()
 * @method StatisticEntity|null get(string $key)
 * @method StatisticEntity|null first()
 * @method StatisticEntity|null last()
 */
class StatisticCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return StatisticEntity::class;
    }
}
