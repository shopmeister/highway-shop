<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Stock;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;

class ExportStockByPsrMessage implements MessagePsrInterface
{
    protected PsrProductCollection $psr;

    /**
     * @return PsrProductCollection
     */
    public function getPsr(): PsrProductCollection
    {
        return $this->psr;
    }

    /**
     * @param PsrProductCollection $psr
     * @return ExportStockByPsrMessage
     */
    public function setPsr(PsrProductCollection $psr): self
    {
        $this->psr = $psr;
        return $this;
    }

    public function getLimit(): int
    {
        return 100;
    }

    public function productIsRequired(): bool
    {
        return false;
    }
}