<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility;

use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;

class CompatibilityFactory
{

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @var SalesChannelContextServiceInterface
     */
    private $salesChannelContextService;


    /**
     * @param string $shopwareVersion
     * @param SalesChannelContextServiceInterface $salesChannelContextService
     */
    public function __construct(string $shopwareVersion, SalesChannelContextServiceInterface $salesChannelContextService)
    {
        $this->shopwareVersion = $shopwareVersion;
        $this->salesChannelContextService = $salesChannelContextService;
    }

}
