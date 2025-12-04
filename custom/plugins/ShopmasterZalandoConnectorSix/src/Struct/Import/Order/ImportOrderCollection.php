<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order;

use ShopmasterZalandoConnectorSix\Struct\Collection;

class ImportOrderCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return ImportOrderStruct::class;
    }

}