<?php declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\EventListeners;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntitySearchResultLoadedEvent;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AmazonPayAvailabilityListener implements EventSubscriberInterface
{
    private ConfigServiceInterface $configService;

    private LoggerInterface $logger;

    public function __construct(
        ConfigServiceInterface $configService,
        LoggerInterface $logger
    ) {
        $this->configService = $configService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.payment_method.search.result.loaded' => ['onSalesChannelSearchResultLoaded', -1],
        ];
    }

    public function onSalesChannelSearchResultLoaded(SalesChannelEntitySearchResultLoadedEvent $event): void
    {
        try {
            $config = $this->configService->getPluginConfig($event->getSalesChannelContext()->getSalesChannel()->getId());
        } catch (ConfigValidationException $e) {
            $this->logger->error('Invalid plugin configuration', ['field' => $e->getField()]);
            $this->removeSwagAmazonPayPaymentMethod($event);
            return;
        }

        if ($event->getSalesChannelContext()->getSalesChannel()->getPaymentMethodId() === PaymentMethodInstaller::AMAZON_PAYMENT_ID) {
            return;
        }

        if ($config->hideOneClickCheckoutButtons() === true) {
            $this->removeSwagAmazonPayPaymentMethod($event);
        }
    }

    private function removeSwagAmazonPayPaymentMethod(SalesChannelEntitySearchResultLoadedEvent $event): void
    {
        $filter = static function (PaymentMethodEntity $entity): bool {
            return $entity->getId() !== PaymentMethodInstaller::AMAZON_PAYMENT_ID;
        };

        $filteredPaymentMethods = $event->getResult()->getEntities()->filter($filter);

        $event->getResult()->assign([
            'total' => \count($filteredPaymentMethods),
            'entities' => $filteredPaymentMethods,
            'elements' => $filteredPaymentMethods->getElements(),
        ]);
    }
}
