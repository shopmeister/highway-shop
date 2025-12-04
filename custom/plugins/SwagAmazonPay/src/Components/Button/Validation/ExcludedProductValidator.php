<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Validation;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;

class ExcludedProductValidator
{
    protected ConfigServiceInterface $configService;

    protected SalesChannelRepository $productRepository;

    public function __construct(
        ConfigServiceInterface $configService,
        SalesChannelRepository $productRepository
    ) {
        $this->configService = $configService;
        $this->productRepository = $productRepository;
    }

    public function eventContainsExcludedProducts(PageLoadedEvent $event): bool
    {
        try {
            $pluginConfig = $this->configService->getPluginConfig($event->getSalesChannelContext()->getSalesChannel()->getId());
        } catch (ConfigValidationException $e) {
            return false;
        }

        if ($pluginConfig->getExcludedProductIds() === [] && $pluginConfig->getExcludedProductStreamIds() === []) {
            return false;
        }

        if ($event instanceof ProductPageLoadedEvent) {
            return $this->productPageContainsExcludedProduct($event, $pluginConfig);
        }

        if ($event instanceof CheckoutCartPageLoadedEvent
            || $event instanceof OffcanvasCartPageLoadedEvent
            || $event instanceof CheckoutConfirmPageLoadedEvent
            || $event instanceof CheckoutRegisterPageLoadedEvent
        ) {
            return $this->cartContainsExcludedProduct($event->getPage()->getCart(), $event->getSalesChannelContext());
        }

        return false;
    }

    public function cartContainsExcludedProduct(Cart $cart, SalesChannelContext $salesChannelContext): bool
    {
        try {
            $config = $this->configService->getPluginConfig($salesChannelContext->getSalesChannel()->getId());
        } catch (ConfigValidationException $e) {
            return false;
        }

        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            $referencedId = $lineItem->getReferencedId();
            if ($referencedId === null) {
                continue;
            }

            $product = $this->fetchProduct($referencedId, $salesChannelContext);
            if ($product === null) {
                continue;
            }

            if ($this->productExcluded($product, $config, $salesChannelContext)) {
                return true;
            }
        }

        return false;
    }

    protected function productExcluded(SalesChannelProductEntity $product, AmazonPayConfigStruct $config, SalesChannelContext $salesChannelContext): bool
    {
        $productExcludedById = \in_array($product->getId(), $config->getExcludedProductIds(), true);
        if ($productExcludedById) {
            return true;
        }

        $parentId = $product->getParentId();
        if ($parentId !== null && \in_array($parentId, $config->getExcludedProductIds(), true)) {
            return true;
        }

        $variantStreams = new ProductStreamCollection();
        if ($parentId !== null) {
            $variantStreams = $this->getProductStreams($parentId, $salesChannelContext);
        }

        $productStreams = $this->getProductStreams($product->getId(), $salesChannelContext);
        if ($productStreams->count() + $variantStreams->count() <= 0) {
            return false;
        }

        return \count(
            \array_intersect(
                $config->getExcludedProductStreamIds(),
                \array_filter(
                    \array_merge(
                        $productStreams->getIds(),
                        $variantStreams->getIds()
                    )
                )
            )
        ) >= 1;
    }

    private function getProductStreams(string $productId, SalesChannelContext $salesChannelContext): ProductStreamCollection
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('streams');

        /** @var SalesChannelProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext)->first();
        if ($product === null) {
            return new ProductStreamCollection();
        }

        return $product->getStreams() ?? new ProductStreamCollection();
    }

    private function productPageContainsExcludedProduct(ProductPageLoadedEvent $event, AmazonPayConfigStruct $config): bool
    {
        $product = $event->getPage()->getProduct();

        return $this->productExcluded($product, $config, $event->getSalesChannelContext());
    }

    private function fetchProduct(string $referencedId, SalesChannelContext $salesChannelContext): ?SalesChannelProductEntity
    {
        $criteria = new Criteria([$referencedId]);

        /** @var SalesChannelProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext)->first();

        return $product;
    }
}
