<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PurePaymentMethod\Hydrator;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession\CreateCheckoutSessionHydrator;
use Swag\AmazonPay\Components\Client\Hydrator\Request\UpdateCheckoutSession\UpdateCheckoutSessionHydrator;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Components\PurePaymentMethod\Struct\AmazonPayPurePaymentMethodPayloadStruct;
use Swag\AmazonPay\Util\Config\VersionProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AmazonPayPurePaymentMethodPayloadHydrator implements AmazonPayPurePaymentMethodPayloadHydratorInterface
{
    private ConfigServiceInterface $configService;

    private VersionProviderInterface $versionProvider;

    private RouterInterface $router;

    private LoggerInterface $logger;

    public function __construct(
        ConfigServiceInterface $configService,
        VersionProviderInterface $versionProvider,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->configService = $configService;
        $this->versionProvider = $versionProvider;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function hydrate(SalesChannelContext $salesChannelContext, CustomerEntity $customerEntity, OrderTransactionEntity $orderTransaction): ?AmazonPayPurePaymentMethodPayloadStruct
    {
        try {
            $pluginConfig = $this->configService->getPluginConfig($salesChannelContext->getSalesChannel()->getId());
        } catch (ConfigValidationException $e) {
            $this->logger->error('Could not generate Amazon login button payload', ['Exception' => $e->getMessage()]);

            return null;
        }

        $order = $orderTransaction->getOrder();
        if ($order === null) {
            $this->logger->error('Invalid order for pure payment method');

            return null;
        }

        $versions = $this->versionProvider->getVersions($salesChannelContext->getContext());

        $payload = new AmazonPayPurePaymentMethodPayloadStruct();
        $payload->setCheckoutResultReturnUrl(
            $this->router->generate(
                'payment.amazon.finalize.transaction',
                [
                    'orderTransactionId' => $orderTransaction->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        $payload->setCheckoutCancelUrl(
            $this->router->generate(
                'payment.amazon.finalize.transaction',
                [
                    'orderTransactionId' => $orderTransaction->getId(),
                    CreateCheckoutSessionHydrator::CUSTOMER_CANCELLED_PARAMETER => true,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
        $payload->setStoreId($pluginConfig->getClientId());
        $payload->setCustomer($customerEntity);
        $payload->setTotalPrice($orderTransaction->getAmount()->getTotalPrice());
        $payload->setCurrencyCode($salesChannelContext->getCurrency()->getIsoCode());
        $payload->setOrderNumber($order->getOrderNumber());
        $payload->setStoreName(\mb_substr((string) $this->configService->getSystemConfig('core.basicInformation.shopName', $salesChannelContext->getSalesChannel()->getId()), 0, UpdateCheckoutSessionHydrator::MERCHANT_STORE_NAME_MAX_CHARACTERS));
        $payload->setCustomInformation(\sprintf('Created by shopware AG, Shopware %s, %s', $versions['shopware'], $versions['plugin']));
        $payload->setCanHandlePendingAuth($pluginConfig->canHandlePendingAuth());

        return $payload;
    }
}
