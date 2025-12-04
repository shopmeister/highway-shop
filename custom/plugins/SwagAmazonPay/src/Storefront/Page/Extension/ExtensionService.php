<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Storefront\Page\Extension;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\PurePaymentMethod\Hydrator\AmazonPayPurePaymentMethodPayloadHydratorInterface;
use Swag\AmazonPay\Util\Language\LanguageProviderInterface;

class ExtensionService
{
    private AmazonPayPurePaymentMethodPayloadHydratorInterface $payloadHydrator;

    private ClientProviderInterface $clientProvider;

    private ConfigServiceInterface $configService;

    private LanguageProviderInterface $languageProvider;

    public function __construct(
        AmazonPayPurePaymentMethodPayloadHydratorInterface $payloadHydrator,
        ClientProviderInterface $clientProvider,
        ConfigServiceInterface $configService,
        LanguageProviderInterface $languageProvider
    ) {
        $this->payloadHydrator = $payloadHydrator;
        $this->clientProvider = $clientProvider;
        $this->configService = $configService;
        $this->languageProvider = $languageProvider;
    }

    public function getPurePaymentExtension(SalesChannelContext $salesChannelContext, OrderTransactionEntity $orderTransaction, bool $isSecureRequest, ?string $customReturnUrl = null): ?AmazonPurePaymentExtension
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        $extension = new AmazonPurePaymentExtension();

        $this->setBaseButtonExtensionValues(
            $extension,
            $this->languageProvider->getAmazonPayButtonLanguage($salesChannelContext->getSalesChannel()->getLanguageId(), $salesChannelContext->getContext()),
            $isSecureRequest,
            $salesChannelId
        );

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return null;
        }

        $payloadStruct = $this->payloadHydrator->hydrate($salesChannelContext, $customer, $orderTransaction);
        if ($payloadStruct === null) {
            $payload = '';
        } else {
            if($customReturnUrl){
                $payloadStruct->setCheckoutResultReturnUrl($customReturnUrl);
                $payloadStruct->setCheckoutCancelUrl($customReturnUrl);
            }
            $payload = \json_encode(
                $payloadStruct,
                \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
            );

            if (!\is_string($payload)) {
                $payload = '';
            }
        }

        $extension->setPayload($payload);
        $extension->setSignature(
            $this->clientProvider->getClient($salesChannelId)->generateButtonSignature($extension->getPayload())
        );

        return $extension;
    }

    public function setBaseButtonExtensionValues(
        AbstractAmazonButtonExtension $buttonExtension,
        string $checkoutLanguage,
        bool $isSecureRequest,
        ?string $salesChannelId
    ): void {
        $pluginConfig = $this->configService->getPluginConfig($salesChannelId);

        $buttonExtension->setMerchantId($pluginConfig->getMerchantId());
        $buttonExtension->setCheckoutLanguage($checkoutLanguage);
        $buttonExtension->setLedgerCurrency($pluginConfig->getLedgerCurrency() ?? AmazonPayConfigStruct::LEDGER_CURRENCY_EU);
        $buttonExtension->setSandbox($pluginConfig->isSandboxActive());
        $buttonExtension->setHideButton($pluginConfig->hideOneClickCheckoutButtons());
        $buttonExtension->setPublicKeyId($pluginConfig->getPublicKeyId());
        $buttonExtension->setButtonColor($pluginConfig->getButtonColor());
        $buttonExtension->setSecureRequest($isSecureRequest);
    }
}
