<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Price;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;

class ImportPriceReportByPsrMessage implements MessagePsrInterface
{

    private PsrProductCollection $psr;

    /**
     * @return PsrProductCollection
     */
    public function getPsr(): PsrProductCollection
    {
        return $this->psr;
    }

    /**
     * @param PsrProductCollection $psr
     * @return $this
     */
    public function setPsr(PsrProductCollection $psr): self
    {
        $this->psr = $psr;
        return $this;
    }

    public function getLimit(): int
    {
        return 10; //max 10 ean
    }

    public function productIsRequired(): bool
    {
        return true;
    }
}