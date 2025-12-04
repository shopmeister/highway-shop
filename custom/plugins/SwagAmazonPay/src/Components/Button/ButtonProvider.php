<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\Button;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\State;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoadedEvent;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoadedEvent;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Swag\AmazonPay\Components\Button\Login\Hydrator\AmazonLoginButtonPayloadHydratorInterfaceV3;
use Swag\AmazonPay\Components\Button\Pay\Hydrator\AmazonPayButtonPayloadHydratorInterfaceV2;
use Swag\AmazonPay\Components\Button\Validation\ExcludedProductValidator;
use Swag\AmazonPay\Components\Cart\CartService;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Installer\RuleInstaller;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonLoginButtonExtension;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonPayButtonExtension;
use Swag\AmazonPay\Storefront\Page\Extension\ExtensionService;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelperInterface;
use Swag\AmazonPay\Util\Language\LanguageProviderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ButtonProvider implements ButtonProviderInterface
{
    private AmazonPayConfigStruct $pluginConfig;

    public function __construct(
        private readonly ConfigServiceInterface                      $configService,
        private readonly LanguageProviderInterface                   $languageProvider,
        private readonly RouterInterface                             $router,
        private readonly AmazonPayPaymentMethodHelperInterface       $paymentMethodHelper,
        private readonly ClientProviderInterface                     $clientProvider,
        private readonly AmazonPayButtonPayloadHydratorInterfaceV2   $payButtonPayloadHydrator,
        private readonly AmazonLoginButtonPayloadHydratorInterfaceV3 $loginButtonPayloadHydrator,
        private readonly LoggerInterface                             $logger,
        private readonly ExcludedProductValidator                    $excludedProductValidator,
        private readonly ExtensionService                            $extensionService,
        private readonly CartService                                 $cartService,
        private readonly string                                      $shopwareVersion,
        private readonly CacheInterface                              $cache,
    )
    {
    }

    public function getAmazonPayButton(PageLoadedEvent $event, ?string $customReviewUrl = null): ?AmazonPayButtonExtension
    {
        if (false === $this->isAmazonPayButtonAvailable($event)) {
            return null;
        }

        if ($this->excludedProductValidator->eventContainsExcludedProducts($event)) {
            return null;
        }

        $buttonExtension = new AmazonPayButtonExtension();
        $this->extensionService->setBaseButtonExtensionValues(
            $buttonExtension,
            $this->getCheckoutLanguage($event),
            $event->getRequest()->isSecure(),
            $event->getSalesChannelContext()->getSalesChannel()->getId()
        );

        $buttonExtension->setAddLineItemUrl($this->router->generate('frontend.checkout.line-item.add'));
        $buttonExtension->setIsShopware65(version_compare($this->shopwareVersion, '6.5.0', '>='));
        $hasCartPhysicalProducts = $this->cartService->hasCartPhysicalProducts($event->getSalesChannelContext());
        $buttonExtension->setProductType($hasCartPhysicalProducts ? AmazonPayButtonExtension::PRODUCT_TYPE_PAY_AND_SHIP : AmazonPayButtonExtension::PRODUCT_TYPE_PAY_ONLY);

        $this->setEventSpecificButtonParameters($event, $buttonExtension);

        $salesChannelContext = $event->getSalesChannelContext();
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        try {
            $payload = $this->payButtonPayloadHydrator->hydratePayload(
                $salesChannelId,
                $salesChannelContext->getContext(),
                true,
                $customReviewUrl
            );

            $payloadJson = (string)\json_encode($payload, \JSON_UNESCAPED_SLASHES | \JSON_FORCE_OBJECT);
            $signature = $this->getButtonSignature(
                $salesChannelId,
                $payloadJson
            );
        } catch (\Throwable $e) {
            $this->logger->error('Could not get button signature', ['Exception' => $e->getMessage()]);

            return null;
        }

        $buttonExtension->setPayload($payloadJson);
        $buttonExtension->setSignature($signature);

        return $buttonExtension;
    }

    protected function setEventSpecificButtonParameters(PageLoadedEvent $event, AmazonPayButtonExtension $buttonExtension): void
    {
        // Per default the placement is set to Swag\AmazonPay\Storefront\Page\Extension\AbstractAmazonButtonExtension::BUTTON_PLACEMENT_OTHER
        if ($event instanceof CheckoutCartPageLoadedEvent || $event instanceof OffcanvasCartPageLoadedEvent) {
            $buttonExtension->setPlacement(AmazonPayButtonExtension::BUTTON_PLACEMENT_CART);
            $buttonExtension->setEstimatedOrderAmount(
                $this->getEstimatedOrderAmount($event->getSalesChannelContext())
            );
        } elseif ($event instanceof ProductPageLoadedEvent) {
            $buttonExtension->setPlacement(AmazonPayButtonExtension::BUTTON_PLACEMENT_PRODUCT);
            $productEntity = $event->getPage()->getProduct();
            if (in_array(State::IS_PHYSICAL, $productEntity->getStates() ?? [])) {
                $buttonExtension->setProductType(AmazonPayButtonExtension::PRODUCT_TYPE_PAY_AND_SHIP);
            }
            $buttonExtension->setEstimatedOrderAmount(
                $this->getEstimatedOrderAmount($event->getSalesChannelContext(), $event->getPage()->getProduct()->getCalculatedPrice()->getTotalPrice())
            );

            try {
                $this->pluginConfig = $this->configService->getPluginConfig($event->getSalesChannelContext()->getSalesChannelId());
                $buttonExtension->setIsListingButtonEnabled($this->pluginConfig->isDisplayButtonOnListingPage());
            } catch (ConfigValidationException) {
                // Do nothing
            }
        } elseif ($event instanceof SearchPageLoadedEvent || $event instanceof NavigationPageLoadedEvent || $event instanceof GenericPageLoadedEvent) {
            $buttonExtension->setPlacement(AmazonPayButtonExtension::BUTTON_PLACEMENT_PRODUCT);
            $buttonExtension->setProductType(AmazonPayButtonExtension::PRODUCT_TYPE_PAY_AND_SHIP);
        } elseif ($event instanceof CheckoutRegisterPageLoadedEvent) {
            $buttonExtension->setPlacement(AmazonPayButtonExtension::BUTTON_PLACEMENT_CHECKOUT);
            $buttonExtension->setEstimatedOrderAmount(
                $this->getEstimatedOrderAmount($event->getSalesChannelContext())
            );
        }
    }

    /**
     * @param AccountLoginPageLoadedEvent|AccountProfilePageLoadedEvent $event
     */
    public function getAmazonLoginButton(PageLoadedEvent $event): ?AmazonLoginButtonExtension
    {
        if (false === $this->isAmazonLoginButtonAvailable($event)) {
            return null;
        }

        $salesChannelContext = $event->getSalesChannelContext();
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        try {
            $payloadJson = (string)\json_encode(
                $this->loginButtonPayloadHydrator->hydrate($salesChannelId, $salesChannelContext->getContext(), $event),
                \JSON_UNESCAPED_SLASHES
            );

            $signature = $this->getButtonSignature($salesChannelId, $payloadJson);
        } catch (\Throwable $e) {
            $this->logger->error('Could not get button signature', ['Exception' => $e->getMessage()]);

            return null;
        }

        $buttonExtension = new AmazonLoginButtonExtension();
        $this->extensionService->setBaseButtonExtensionValues(
            $buttonExtension,
            $this->getCheckoutLanguage($event),
            $event->getRequest()->isSecure(),
            $event->getSalesChannelContext()->getSalesChannel()->getId()
        );

        $buttonExtension->setPlacement(AmazonPayButtonExtension::BUTTON_PLACEMENT_OTHER);
        $buttonExtension->setPayload($payloadJson);
        $buttonExtension->setSignature($signature);

        return $buttonExtension;
    }

    private function isAmazonLoginButtonAvailable(PageLoadedEvent $event): bool
    {
        $salesChannel = $event->getSalesChannelContext()->getSalesChannel();

        try {
            $this->pluginConfig = $this->configService->getPluginConfig($salesChannel->getId());
        } catch (ConfigValidationException) {
            return false;
        }

        if (!$this->pluginConfig->isDisplayLoginButtonOnRegistrationPage()) {
            return false;
        }

        return true;
    }

    private function isAmazonPayButtonAvailable(PageLoadedEvent $event): bool
    {
        $currencyIsoCode = $event->getSalesChannelContext()->getCurrency()->getIsoCode();
        if (!\in_array($currencyIsoCode, RuleInstaller::VALID_CURRENCIES, true)) {
            return false;
        }

        // Payment method inactive or unassigned to sales channel
        if (false === $this->paymentMethodHelper->isAmazonPayActive($event->getSalesChannelContext())) {
            return false;
        }

        $salesChannel = $event->getSalesChannelContext()->getSalesChannel();

        // Invalid plugin configuration
        try {
            $this->pluginConfig = $this->configService->getPluginConfig($salesChannel->getId());
        } catch (ConfigValidationException) {
            return false;
        }

        if ($event instanceof ProductPageLoadedEvent && !$this->pluginConfig->isDisplayButtonOnProductPage()) {
            return false;
        }
        $isListing = $event instanceof SearchPageLoadedEvent || $event instanceof NavigationPageLoadedEvent;
        if ($event instanceof GenericPageLoadedEvent) {
            $context = $event->getContext();
            $isListing = null !== $context->getExtension('amazonPayIsListing');
        }

        if ($isListing && !$this->pluginConfig->isDisplayButtonOnListingPage()) {
            return false;
        }

        if ($event instanceof CheckoutRegisterPageLoadedEvent && !$this->pluginConfig->isDisplayButtonOnCheckoutRegisterPage()) {
            return false;
        }

        return true;
    }

    private function getCheckoutLanguage(PageLoadedEvent $event): string
    {
        $salesChannelContext = $event->getSalesChannelContext();
        if (method_exists($salesChannelContext, 'getLanguageId')) {
            $languageId = $salesChannelContext->getLanguageId();
        } elseif (method_exists($salesChannelContext, 'getLanguageIdChain')) {
            $languageId = $salesChannelContext->getLanguageIdChain()[0];
        }
        if (empty($languageId)) {
            $languageId = $salesChannelContext->getSalesChannel()->getLanguageId();
        }

        return $this->languageProvider->getAmazonPayButtonLanguage(
            $languageId,
            $event->getContext(),
            $salesChannelContext->getSalesChannel()->getId()
        );
    }

    private function getButtonSignature(string $salesChannelId, string $payload): string
    {
        $pluginConfig = $this->configService->getPluginConfig($salesChannelId);
        $cacheKey = 'amazon_pay_button_signature_' . $salesChannelId . '_' . $pluginConfig->getPublicKeyId() . '_' . \md5($payload);

        return $this->cache->get($cacheKey, function () use ($salesChannelId, $payload) {
            $client = $this->clientProvider->getClient($salesChannelId);

            return $client->generateButtonSignature($payload);
        });
    }

    private function getEstimatedOrderAmount(SalesChannelContext $salesChannelContext, ?float $productPrice = null): ?array
    {
        if ('EUR' !== $salesChannelContext->getCurrency()->getIsoCode()) {
            return null;
        }

        $total = $this->cartService->getCart($salesChannelContext)->getPrice()->getTotalPrice();

        if ($productPrice) {
            $total += $productPrice;
        }

        return [
            'amount' => \number_format($total, 2, '.', ''),
            'currencyCode' => $salesChannelContext->getCurrency()->getIsoCode(),
        ];
    }
}
