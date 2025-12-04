<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification;

use Swag\AmazonPay\Components\PaymentNotification\Handler\PaymentNotificationHandlerInterface;

class PaymentNotificationHandlerRegistry implements PaymentNotificationHandlerRegistryInterface
{
    /**
     * @var PaymentNotificationHandlerInterface[]
     */
    private array $handler = [];

    /**
     * {@inheritdoc}
     */
    public function getHandler(string $objectType): ?PaymentNotificationHandlerInterface
    {
        foreach ($this->handler as $handler) {
            if ($handler->supports($objectType)) {
                return $handler;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setHandlers(array $handler): void
    {
        $this->handler = $handler;
    }
}
