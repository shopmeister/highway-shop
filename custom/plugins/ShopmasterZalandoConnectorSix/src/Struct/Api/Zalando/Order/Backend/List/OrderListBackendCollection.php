<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\List;

use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderCollection;

class OrderListBackendCollection extends OrderCollection
{
    protected function getExpectedClass(): ?string
    {
        return OrderListBackendStruct::class;
    }

}