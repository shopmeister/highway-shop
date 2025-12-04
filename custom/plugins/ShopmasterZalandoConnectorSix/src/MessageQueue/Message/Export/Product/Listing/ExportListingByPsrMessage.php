<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Export\Product\Listing;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;

class ExportListingByPsrMessage implements MessagePsrInterface
{
    private PsrProductCollection $psrProductCollection;

    public function __construct(
        private bool $isDryRun = false,
        private ?string $specificSalesChannelId = null
    ) {
        $this->psrProductCollection = new PsrProductCollection();
    }

    public function setPsr(PsrProductCollection $psr): self
    {
        $this->psrProductCollection = $psr;
        return $this;
    }

    public function getPsr(): PsrProductCollection
    {
        return $this->psrProductCollection;
    }

    public function getLimit(): int
    {
        return 100;
    }

    public function productIsRequired(): bool
    {
        return true;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function getSpecificSalesChannelId(): ?string
    {
        return $this->specificSalesChannelId;
    }
}