<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\SalesChannel\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swkweb\HideSoldoutProducts\Core\Content\Product\SalesChannel\ProductAvailability\ProductAvailabilityCriteriaBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

final class SalesChannelCriteriaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $config,
        private readonly ProductAvailabilityCriteriaBuilder $criteriaBuilder,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LISTING_CRITERIA => 'onProductListingCriteria',
            ProductEvents::PRODUCT_SEARCH_CRITERIA => 'onProductSearchCriteria',
            ProductEvents::PRODUCT_SUGGEST_CRITERIA => 'onProductSearchCriteria',
            'sales_channel.product.process.criteria' => 'processProductCriteria',
        ];
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        $salesChannelId = $salesChannelContext->getSalesChannelId();
        if (!$this->config->getBool('SwkwebHideSoldoutProducts.config.hideListing', $salesChannelId)) {
            return;
        }

        $navigationId = $this->getNavigationId($event->getRequest(), $salesChannelContext);
        $categoryExemptions = $this->config->get('SwkwebHideSoldoutProducts.config.categoryExemptions', $salesChannelId);

        if (is_array($categoryExemptions) && in_array($navigationId, $categoryExemptions, true)) {
            return;
        }

        $this->criteriaBuilder->addNotSoldoutFilter($event->getCriteria(), $salesChannelId);
    }

    public function onProductSearchCriteria(ProductListingCriteriaEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        if (!$this->config->getBool('SwkwebHideSoldoutProducts.config.hideSearch', $salesChannelId)) {
            return;
        }

        $this->criteriaBuilder->addNotSoldoutFilter($event->getCriteria(), $salesChannelId);
    }

    public function processProductCriteria(SalesChannelProcessCriteriaEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        if (!$this->config->getBool('SwkwebHideSoldoutProducts.config.hideGlobal', $salesChannelId)) {
            return;
        }

        $this->criteriaBuilder->addNotSoldoutFilter($event->getCriteria(), $salesChannelId);
    }

    private function getNavigationId(Request $request, SalesChannelContext $salesChannelContext): string
    {
        $navigationId = $request->get('navigationId');
        if (is_string($navigationId)) {
            return $navigationId;
        }

        $params = $request->attributes->get('_route_params');
        if (is_array($params) && isset($params['navigationId']) && is_string($params['navigationId'])) {
            return $params['navigationId'];
        }

        return $salesChannelContext->getSalesChannel()->getNavigationCategoryId();
    }
}
