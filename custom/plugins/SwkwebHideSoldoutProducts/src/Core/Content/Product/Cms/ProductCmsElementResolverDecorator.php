<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\CmsElementResolverInterface;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swkweb\HideSoldoutProducts\Core\Content\Product\SalesChannel\ProductAvailability\ProductAvailabilityCriteriaBuilder;

final class ProductCmsElementResolverDecorator implements CmsElementResolverInterface
{
    public function __construct(
        private readonly CmsElementResolverInterface $resolver,
        private readonly SystemConfigService $config,
        private readonly ProductAvailabilityCriteriaBuilder $criteriaBuilder,
    ) {}

    public function getType(): string
    {
        return $this->resolver->getType();
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $criteriaCollection = $this->resolver->collect($slot, $resolverContext);
        $salesChannelId = $resolverContext->getSalesChannelContext()->getSalesChannelId();

        if ($criteriaCollection === null
            || !$this->config->getBool('SwkwebHideSoldoutProducts.config.hideCms', $salesChannelId)) {
            return $criteriaCollection;
        }

        foreach ($criteriaCollection->all()[ProductDefinition::class] ?? [] as $criteria) {
            $this->criteriaBuilder->addNotSoldoutFilter($criteria, $salesChannelId);
        }

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $this->resolver->enrich($slot, $resolverContext, $result);
    }
}
