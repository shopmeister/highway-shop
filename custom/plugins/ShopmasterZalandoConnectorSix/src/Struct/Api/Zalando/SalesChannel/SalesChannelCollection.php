<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiCollection;

class SalesChannelCollection extends ApiCollection
{
    public function getActive(): SalesChannelCollection
    {
        return $this->filter(function (SalesChannelStruct $struct) {
            return $struct->isLive();
        });
    }

    protected function getExpectedClass(): ?string
    {
        return SalesChannelStruct::class;
    }
}