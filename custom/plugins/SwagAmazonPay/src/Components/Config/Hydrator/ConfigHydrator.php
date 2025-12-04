<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\Config\Hydrator;

use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;

class ConfigHydrator implements ConfigHydratorInterface
{
    public function hydrate(array $config): AmazonPayConfigStruct
    {
        $config = $this->trimDomain($config);

        return new AmazonPayConfigStruct(
            (string) $config['merchantId'],
            (string) $config['publicKeyId'],
            (string) $config['privateKey'],
            (string) $config['clientId'],
            (bool) $config['sandbox'],
            (bool) $config['hideOneClickCheckoutButtons'],
            (bool) $config['displayButtonOnProductPage'],
            (bool) $config['displayButtonOnListingPage'],
            (bool) $config['displayButtonOnCheckoutRegisterPage'],
            (string) $config['paymentStateMappingCharge'],
            (string) $config['paymentStateMappingPartialCharge'],
            (string) $config['paymentStateMappingRefund'],
            (string) $config['paymentStateMappingPartialRefund'],
            (string) $config['paymentStateMappingCancel'],
            (string) $config['paymentStateMappingAuthorize'],
            (string) $config['authMode'],
            (string) $config['chargeMode'],
            (string) $config['orderChargeTriggerState'],
            (string) $config['orderRefundTriggerState'],
            (string) $config['excludedItems'],
            (bool) $config['sendErrorMail'],
            (string) $config['loggingMode'],
            $config['ledgerCurrency'] ? (string) $config['ledgerCurrency'] : null,
            (string) $config['softDescriptor'],
            (bool) $config['displayLoginButtonOnRegistrationPage'],
            (string) $config['buttonColor'],
            (array) $config['excludedProductIds'],
            (array) $config['excludedProductStreamIds']
        );
    }

    /**
     * Cuts SwagAmazonPay.settings from all configuration keys for an easier use later on.
     */
    private function trimDomain(array $config): array
    {
        $result = [];

        foreach ($config as $key => $value) {
            $key = \str_replace(\sprintf('%s.', ConfigServiceInterface::CONFIG_DOMAIN), '', $key);
            $result[$key] = $value;
        }

        return $result;
    }
}
