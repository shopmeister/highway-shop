<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification;

use Swag\AmazonPay\Components\PaymentNotification\Handler\PaymentNotificationHandlerInterface;

interface PaymentNotificationHandlerRegistryInterface
{
    /**
     * Gets a payment notification handler by the related object type.
     */
    public function getHandler(string $objectType): ?PaymentNotificationHandlerInterface;

    /**
     * @param PaymentNotificationHandlerInterface[] $handler
     */
    public function setHandlers(array $handler): void;
}
