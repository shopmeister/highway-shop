<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\EventListeners;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoadedEvent;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Swag\AmazonPay\Components\Button\ButtonProviderInterface;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonPayButtonExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AmazonPayButtonEventListener implements EventSubscriberInterface
{
    private ButtonProviderInterface $buttonProvider;

    public function __construct(
        ButtonProviderInterface $buttonProvider,
    )
    {
        $this->buttonProvider = $buttonProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutCartPageLoadedEvent::class => 'addAmazonPayButtonExtension',
            OffcanvasCartPageLoadedEvent::class => 'addAmazonPayButtonExtension',
            ProductPageLoadedEvent::class => 'addAmazonPayButtonExtension',
            NavigationPageLoadedEvent::class => 'addAmazonPayButtonExtension',
            SearchPageLoadedEvent::class => 'addAmazonPayButtonExtension',
            CmsPageLoadedEvent::class => 'addAmazonPayButtonExtensionToCmsPage',
            CheckoutRegisterPageLoadedEvent::class => 'addAmazonPayButtonExtension',
        ];
    }

    public function addAmazonPayButtonExtension(PageLoadedEvent $event): void
    {
        $buttonExtension = $this->buttonProvider->getAmazonPayButton($event);
        if ($buttonExtension !== null) {
            $event->getPage()->addExtension(AmazonPayButtonExtension::EXTENSION_NAME, $buttonExtension);
        }
    }

    public function addAmazonPayButtonExtensionToCmsPage(CmsPageLoadedEvent $event): void
    {
        /** @var CmsPageEntity $page */
        foreach ($event->getResult() as $page) {
            if ($page->getType() === 'product_list') {
                $pseudoEvent = new GenericPageLoadedEvent(new Page(), $event->getSalesChannelContext(), $event->getRequest());
                $pseudoEvent->getContext()->addExtension('amazonPayIsListing', new ArrayStruct());
                $buttonExtension = $this->buttonProvider->getAmazonPayButton($pseudoEvent);
                if ($buttonExtension !== null) {
                    $page->addExtension(AmazonPayButtonExtension::EXTENSION_NAME, $buttonExtension);
                }
            }
        }
    }
}
