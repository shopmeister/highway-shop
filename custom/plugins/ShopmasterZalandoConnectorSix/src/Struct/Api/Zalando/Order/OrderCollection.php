<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiCollection;

class OrderCollection extends ApiCollection
{

    protected function getExpectedClass(): ?string
    {
        return OrderStruct::class;
    }

}