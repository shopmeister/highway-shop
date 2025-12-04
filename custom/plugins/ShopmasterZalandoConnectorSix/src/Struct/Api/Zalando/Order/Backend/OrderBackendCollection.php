<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend;

use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderCollection;

class OrderBackendCollection extends OrderCollection
{
    protected function getExpectedClass(): ?string
    {
        return OrderBackendStruct::class;
    }

}