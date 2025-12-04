<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Storefront\Pagelet\Footer;

use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FooterPageletLoadedEventSubscriber implements EventSubscriberInterface
{
    public const AMAZON_PAY_FOOTER_EXTENSION = 'amazonPayFooterExtension';

    private AmazonPayPaymentMethodHelperInterface $amazonPayPaymentMethodHelper;

    private ConfigServiceInterface $configService;

    public function __construct(
        AmazonPayPaymentMethodHelperInterface $amazonPayPaymentMethodHelper,
        ConfigServiceInterface $configService
    ) {
        $this->amazonPayPaymentMethodHelper = $amazonPayPaymentMethodHelper;
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FooterPageletLoadedEvent::class => 'onFooterPageletLoaded',
        ];
    }

    public function onFooterPageletLoaded(FooterPageletLoadedEvent $event): void
    {
        if (!$this->amazonPayPaymentMethodHelper->isAmazonPayActive($event->getSalesChannelContext())) {
            return;
        }

        try {
            $amazonPayConfigStruct = $this->configService->getPluginConfig($event->getSalesChannelContext()->getSalesChannel()->getId());
        } catch (ConfigValidationException $e) {
            return;
        }

        if ($amazonPayConfigStruct->isHideOneClickCheckoutButtons()) {
            return;
        }

        $event->getPagelet()->addExtension(self::AMAZON_PAY_FOOTER_EXTENSION, new AmazonPayFooterStruct());
    }
}
