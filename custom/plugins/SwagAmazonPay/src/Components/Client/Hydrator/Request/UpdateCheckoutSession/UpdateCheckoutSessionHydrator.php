<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Hydrator\Request\UpdateCheckoutSession;

use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;
use Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession\CreateCheckoutSessionHydrator;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;
use Swag\AmazonPay\Util\Config\VersionProviderInterface;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelperInterface;
use Swag\AmazonPay\Util\Util;

class UpdateCheckoutSessionHydrator implements UpdateCheckoutSessionHydratorInterface
{
    public const MERCHANT_STORE_NAME_MAX_CHARACTERS = 50;

    private ConfigServiceInterface $configService;

    private VersionProviderInterface $versionProvider;

    public function __construct(
        ConfigServiceInterface $configService,
        VersionProviderInterface $versionProvider
    ) {
        $this->configService = $configService;
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        AsyncPaymentTransactionStruct $pendingShopwareTransaction,
        CurrencyEntity $currency,
        Context $context,
        string $paymentIntent = self::PAYMENT_INTENT_AUTHORIZE,
        string $noteToBuyer = ''
    ): array {
        $salesChannelId = $pendingShopwareTransaction->getOrder()->getSalesChannelId();
        $transaction = $pendingShopwareTransaction->getOrderTransaction();
        $customFields = (array) $transaction->getCustomFields();

        $checkoutId = $customFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHECKOUT_ID] ?? '';
        $versions = $this->versionProvider->getVersions($context);

        $pluginConfig = $this->configService->getPluginConfig($salesChannelId);


        return [
            'webCheckoutDetails' => [
                'checkoutResultReturnUrl' => \sprintf(
                    '%s&amazonPayCheckoutId=%s',
                    $pendingShopwareTransaction->getReturnUrl(),
                    $checkoutId
                ),
                'checkoutCancelUrl' => \sprintf(
                    '%s&%s',
                    $pendingShopwareTransaction->getReturnUrl(),
                    CreateCheckoutSessionHydrator::CUSTOMER_CANCELLED_PARAMETER
                ),
            ],
            'paymentDetails' => [
                'paymentIntent' => $paymentIntent,
                'canHandlePendingAuthorization' => $pluginConfig->canHandlePendingAuth(),
                'chargeAmount' => [
                    'amount' => Util::round(
                        $pendingShopwareTransaction->getOrder()->getAmountTotal(),
                        AmazonPayPaymentMethodHelperInterface::DEFAULT_DECIMAL_PRECISION
                    ),
                    'currencyCode' => $currency->getIsoCode(),
                ],
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $pendingShopwareTransaction->getOrder()->getOrderNumber(),
                'merchantStoreName' => \mb_substr((string) $this->configService->getSystemConfig('core.basicInformation.shopName', $salesChannelId), 0, self::MERCHANT_STORE_NAME_MAX_CHARACTERS),
                'noteToBuyer' => $noteToBuyer,
                'customInformation' => \sprintf('Created by shopware AG, Shopware %s, %s', $versions['shopware'], $versions['plugin']),
            ],
            'platformId' => ConfigServiceInterface::PLATFORM_ID,
        ];
    }
}
