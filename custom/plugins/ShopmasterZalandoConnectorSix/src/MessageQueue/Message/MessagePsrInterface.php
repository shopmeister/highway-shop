<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message;

use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;

interface MessagePsrInterface extends MessageInterface
{
    /**
     * @return PsrProductCollection
     */
    public function getPsr(): PsrProductCollection;

    /**
     * @param PsrProductCollection $psr
     * @return $this
     */
    public function setPsr(PsrProductCollection $psr): self;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return bool
     */
    public function productIsRequired(): bool;

}