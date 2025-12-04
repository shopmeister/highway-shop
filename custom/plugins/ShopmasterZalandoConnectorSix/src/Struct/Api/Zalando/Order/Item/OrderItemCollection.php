<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiCollection;

class OrderItemCollection extends ApiCollection
{
    protected function getExpectedClass(): ?string
    {
        return OrderItemStruct::class;
    }

}