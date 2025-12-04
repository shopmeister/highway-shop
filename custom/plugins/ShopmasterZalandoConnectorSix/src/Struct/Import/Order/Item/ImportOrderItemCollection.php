<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Item;

use ShopmasterZalandoConnectorSix\Struct\Collection;

class ImportOrderItemCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return ImportOrderItemStruct::class;
    }

}