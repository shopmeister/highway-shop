<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\Specification\Shopware;


use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface ZalandoOrderCheckInterface
{
    public function isAvailable(OrderEntity $order, Context $context): bool;
}