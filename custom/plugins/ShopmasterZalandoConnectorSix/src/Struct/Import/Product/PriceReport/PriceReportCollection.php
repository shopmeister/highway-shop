<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Product\PriceReport;

use ShopmasterZalandoConnectorSix\Struct\Collection;

class PriceReportCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return PriceReportStruct::class;
    }

}