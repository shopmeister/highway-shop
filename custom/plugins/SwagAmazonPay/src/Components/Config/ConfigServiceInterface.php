<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Config;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigEntity;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;

interface ConfigServiceInterface
{
    /**
     * The configuration domain name.
     */
    public const CONFIG_DOMAIN = 'SwagAmazonPay.settings';
    public const PLATFORM_ID = 'A20SUKFV8Z8AHP';

    /**
     * The default configuration used to merge into the user specific config.
     */
    public const DEFAULT_CONFIG = [
        'merchantId' => '',
        'publicKeyId' => '',
        'privateKey' => '',
        'clientId' => '',
        'sandbox' => false,
        'hideOneClickCheckoutButtons' => false,
        'displayButtonOnProductPage' => true,
        'displayButtonOnListingPage' => true,
        'displayButtonOnCheckoutRegisterPage' => true,
        'paymentStateMappingCharge' => '',
        'paymentStateMappingPartialCharge' => '',
        'paymentStateMappingRefund' => '',
        'paymentStateMappingPartialRefund' => '',
        'paymentStateMappingCancel' => '',
        'paymentStateMappingAuthorize' => '',
        'authMode'=> '',
        'chargeMode' => '',
        'orderChargeTriggerState' => '',
        'orderRefundTriggerState' => '',
        'excludedItems' => '',
        'sendErrorMail' => false,
        'loggingMode' => 'basic',
        'ledgerCurrency' => 'EUR',
        'softDescriptor' => '',
        'displayLoginButtonOnRegistrationPage' => true,
        'inheritFromDefault' => false,
        'buttonColor' => 'Gold',
        'excludedProductIds' => [],
        'excludedProductStreamIds' => [],
    ];

    /**
     * Returns the Amazon Pay plugin configuration.
     *
     * @param string|null $salesChannelId ID of the Shopware sales channel
     *
     * @throws ConfigValidationException
     */
    public function getPluginConfig(?string $salesChannelId = null, bool $skipValidation = false): AmazonPayConfigStruct;

    /**
     * Returns a system configuration value by the given configuration key.
     *
     * @param string $key The key of the config value to be obtained
     */
    public function getSystemConfig(string $key, ?string $salesChannelId = null);

    /**
     * Will return softDescriptor based on config or the shop's name
     *
     * @param string|null $salesChannelId ID of the Shopware sales channel
     */
    public function getSoftDescriptor(?string $salesChannelId = null): string;

    /**
     * Will return the SystemConfigEntity containing a specific merchantId
     */
    public function getConfigEntityByMerchantId(string $merchantId, Context $context): ?SystemConfigEntity;
}
