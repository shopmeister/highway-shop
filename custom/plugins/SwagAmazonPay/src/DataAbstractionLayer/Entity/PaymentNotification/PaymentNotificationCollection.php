<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\DataAbstractionLayer\Entity\PaymentNotification;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                  add(PaymentNotificationEntity $entity)
 * @method void                                  set(string $key, PaymentNotificationEntity $entity)
 * @method \Generator<PaymentNotificationEntity> getIterator()
 * @method PaymentNotificationEntity[]           getElements()
 * @method PaymentNotificationEntity|null        get(string $key)
 * @method PaymentNotificationEntity|null        first()
 * @method PaymentNotificationEntity|null        last()
 */
class PaymentNotificationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PaymentNotificationEntity::class;
    }
}
