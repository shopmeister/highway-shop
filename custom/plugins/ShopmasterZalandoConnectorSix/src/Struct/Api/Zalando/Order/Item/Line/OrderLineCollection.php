<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiCollection;

class OrderLineCollection extends ApiCollection
{
    protected function getExpectedClass(): ?string
    {
        return OrderLineStruct::class;
    }

}