<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Storefront\Page\Product\Subscriber;

use Shopware\Core\Content\Product\Events\ProductCrossSellingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductCrossSellingIdsCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductCrossSellingStreamCriteriaEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swkweb\HideSoldoutProducts\Core\Content\Product\SalesChannel\ProductAvailability\ProductAvailabilityCriteriaBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CrossSellingLoaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $config,
        private readonly ProductAvailabilityCriteriaBuilder $criteriaBuilder,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductCrossSellingStreamCriteriaEvent::class => 'onCrossSellingProductCriteria',
            ProductCrossSellingIdsCriteriaEvent::class => 'onCrossSellingProductCriteria',
        ];
    }

    public function onCrossSellingProductCriteria(ProductCrossSellingCriteriaEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        if (!$this->config->getBool('SwkwebHideSoldoutProducts.config.hideCrossSelling', $salesChannelId)) {
            return;
        }

        $this->criteriaBuilder->addNotSoldoutFilter($event->getCriteria(), $salesChannelId);
    }
}
