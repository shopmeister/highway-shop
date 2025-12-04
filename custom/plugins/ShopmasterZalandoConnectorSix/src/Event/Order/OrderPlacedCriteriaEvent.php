<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Event\Order;

use ShopmasterZalandoConnectorSix\Event\ZalandoEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class OrderPlacedCriteriaEvent extends ZalandoEvent implements ShopwareSalesChannelEvent
{
    public function __construct(
        protected Criteria            $criteria,
        protected SalesChannelContext $context
    )
    {
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}