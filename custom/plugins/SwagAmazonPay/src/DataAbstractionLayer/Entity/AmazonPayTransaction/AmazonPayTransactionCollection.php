<?php

declare(strict_types=1);

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                  add(AmazonPayTransactionEntity $entity)
 * @method void                                  set(string $key, AmazonPayTransactionEntity $entity)
 * @method \Generator<AmazonPayTransactionEntity> getIterator()
 * @method AmazonPayTransactionEntity[]           getElements()
 * @method AmazonPayTransactionEntity|null        get(string $key)
 * @method AmazonPayTransactionEntity|null        first()
 * @method AmazonPayTransactionEntity|null        last()
 */
class AmazonPayTransactionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AmazonPayTransactionEntity::class;
    }
}
