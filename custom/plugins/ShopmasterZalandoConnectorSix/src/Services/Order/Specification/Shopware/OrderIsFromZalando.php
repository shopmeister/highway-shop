<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\Specification\Shopware;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderIsFromZalando implements ZalandoOrderCheckInterface
{

    public function isAvailable(OrderEntity $order, Context $context): bool
    {
        $customFields = $order->getCustomFields() ?? [];
        if (
            !empty($customFields[OrderCustomFields::CUSTOM_FIELD_ORDERNUMBER])
            && !empty($customFields[OrderCustomFields::CUSTOM_FIELD_ORDER_ID])
        ) {
            return true;
        }
        return false;
    }
}