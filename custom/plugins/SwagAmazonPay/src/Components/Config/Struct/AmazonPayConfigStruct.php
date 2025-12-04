<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\Config\Struct;

use Shopware\Core\Framework\Struct\Struct;

final class AmazonPayConfigStruct extends Struct
{
    /**
     * Charge mode for direct charging.
     */
    public const CHARGE_MODE_DIRECT = 'direct';

    /**
     * Charge mode for charging after shipment.
     */
    public const CHARGE_MODE_SHIPPING = 'shipped';

    /**
     * Charge mode for charging manually.
     */
    public const CHARGE_MODE_MANUALLY = 'manually';

    public const AUTH_MODE_CAN_HANDLE_PENDING = 'canHandlePending';
    public const AUTH_MODE_IMMEDIATELY = 'immediately';

    /**
     * Ledger currencies
     */
    public const LEDGER_CURRENCY_EU = 'EUR';
    public const LEDGER_CURRENCY_US = 'USD';
    public const LEDGER_CURRENCY_GBP = 'GBP';
    public const LEDGER_CURRENCY_JP_YEN = 'JPY';


    public const VALID_LEDGER_CURRENCIES = [
        self::LEDGER_CURRENCY_EU,
        self::LEDGER_CURRENCY_US,
        self::LEDGER_CURRENCY_GBP,
        self::LEDGER_CURRENCY_JP_YEN,
    ];


    protected string $merchantId;

    protected string $publicKeyId;

    protected string $privateKey;

    protected string $clientId;

    protected bool $sandboxActive;

    protected bool $hideOneClickCheckoutButtons;

    protected bool $displayButtonOnProductPage;

    protected bool $displayButtonOnListingPage;

    protected bool $displayButtonOnCheckoutRegisterPage;

    protected ?string $paymentStateMappingCharge = null;

    protected ?string $paymentStateMappingPartialCharge = null;

    protected ?string $paymentStateMappingRefund = null;

    protected ?string $paymentStateMappingPartialRefund = null;

    protected ?string $paymentStateMappingCancel = null;

    protected string $authMode;

    protected string $chargeMode;

    protected ?string $paymentStateMappingAuthorize = null;

    protected string $orderChargeTriggerState;

    protected string $orderRefundTriggerState;

    protected string $excludedItems;

    //deprecated?
    protected bool $sendErrorMailActive;

    protected string $loggingMode;

    protected ?string $ledgerCurrency = null;

    protected ?string $softDescriptor = null;

    protected bool $displayLoginButtonOnRegistrationPage;

    protected string $buttonColor;

    protected array $excludedProductIds;

    protected array $excludedProductStreamIds;

    public function __construct(
        string  $merchantId,
        string  $publicKeyId,
        string  $privateKey,
        string  $clientId,
        bool    $sandboxActive,
        bool    $hideOneClickCheckoutButtons,
        bool    $displayButtonOnProductPage,
        bool    $displayButtonOnListingPage,
        bool    $displayButtonOnCheckoutRegisterPage,
        ?string $paymentStateMappingCharge,
        ?string $paymentStateMappingPartialCharge,
        ?string $paymentStateMappingRefund,
        ?string $paymentStateMappingPartialRefund,
        ?string $paymentStateMappingCancel,
        ?string $paymentStateMappingAuthorize,
        string  $authMode,
        string  $chargeMode,
        string  $orderChargeTriggerState,
        string  $orderRefundTriggerState,
        string  $excludedItems,
        bool    $sendErrorMail,
        string  $loggingMode,
        ?string $ledgerCurrency,
        ?string $softDescriptor,
        bool    $displayLoginButtonOnRegistrationPage,
        string  $buttonColor,
        array   $excludedProductIds,
        array   $excludedProductStreamIds
    )
    {
        $this->merchantId = $merchantId;
        $this->publicKeyId = $publicKeyId;
        $this->privateKey = $privateKey;
        $this->clientId = $clientId;
        $this->sandboxActive = $sandboxActive;
        $this->hideOneClickCheckoutButtons = $hideOneClickCheckoutButtons;
        $this->displayButtonOnProductPage = $displayButtonOnProductPage;
        $this->displayButtonOnListingPage = $displayButtonOnListingPage;
        $this->displayButtonOnCheckoutRegisterPage = $displayButtonOnCheckoutRegisterPage;
        $this->paymentStateMappingCharge = $paymentStateMappingCharge;
        $this->paymentStateMappingPartialCharge = $paymentStateMappingPartialCharge;
        $this->paymentStateMappingRefund = $paymentStateMappingRefund;
        $this->paymentStateMappingPartialRefund = $paymentStateMappingPartialRefund;
        $this->paymentStateMappingCancel = $paymentStateMappingCancel;
        $this->paymentStateMappingAuthorize = $paymentStateMappingAuthorize;
        $this->authMode = $authMode;
        $this->chargeMode = $chargeMode;
        $this->orderChargeTriggerState = $orderChargeTriggerState;
        $this->orderRefundTriggerState = $orderRefundTriggerState;
        $this->excludedItems = $excludedItems;
        $this->sendErrorMailActive = $sendErrorMail;
        $this->loggingMode = $loggingMode;
        $this->ledgerCurrency = $ledgerCurrency;
        $this->softDescriptor = $softDescriptor;
        $this->displayLoginButtonOnRegistrationPage = $displayLoginButtonOnRegistrationPage;
        $this->buttonColor = $buttonColor;
        $this->excludedProductIds = $excludedProductIds;
        $this->excludedProductStreamIds = $excludedProductStreamIds;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getPublicKeyId(): string
    {
        return $this->publicKeyId;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function isSandboxActive(): bool
    {
        return $this->sandboxActive;
    }

    public function hideOneClickCheckoutButtons(): bool
    {
        return $this->hideOneClickCheckoutButtons;
    }

    public function getAuthMode(): string
    {
        return $this->authMode;
    }

    public function canHandlePendingAuth(): bool
    {
        return $this->authMode === self::AUTH_MODE_CAN_HANDLE_PENDING;
    }

    public function getChargeMode(): string
    {
        return $this->chargeMode;
    }

    public function isSendErrorMailActive(): bool
    {
        return $this->sendErrorMailActive;
    }

    public function getLoggingMode(): string
    {
        return $this->loggingMode;
    }

    public function getLedgerCurrency(): ?string
    {
        return $this->ledgerCurrency;
    }

    public function getPaymentStateMappingCharge(): ?string
    {
        return $this->paymentStateMappingCharge;
    }

    public function getPaymentStateMappingRefund(): ?string
    {
        return $this->paymentStateMappingRefund;
    }

    public function getPaymentStateMappingPartialRefund(): ?string
    {
        return $this->paymentStateMappingPartialRefund;
    }

    public function getPaymentStateMappingCancel(): ?string
    {
        return $this->paymentStateMappingCancel;
    }

    public function getPaymentStateMappingAuthorize(): ?string
    {
        return $this->paymentStateMappingAuthorize;
    }

    public function getPaymentStateMappingPartialCharge(): ?string
    {
        return $this->paymentStateMappingPartialCharge;
    }

    public function getOrderChargeTriggerState(): string
    {
        return $this->orderChargeTriggerState;
    }

    public function getOrderRefundTriggerState(): string
    {
        return $this->orderRefundTriggerState;
    }

    public function getExcludedItems(): string
    {
        return $this->excludedItems;
    }

    public function getSoftDescriptor(): ?string
    {
        return $this->softDescriptor;
    }

    public function isHideOneClickCheckoutButtons(): bool
    {
        return $this->hideOneClickCheckoutButtons;
    }

    public function setHideOneClickCheckoutButtons(bool $hideOneClickCheckoutButtons): void
    {
        $this->hideOneClickCheckoutButtons = $hideOneClickCheckoutButtons;
    }

    public function isDisplayButtonOnProductPage(): bool
    {
        return $this->displayButtonOnProductPage;
    }

    public function setDisplayButtonOnProductPage(bool $displayButtonOnProductPage): void
    {
        $this->displayButtonOnProductPage = $displayButtonOnProductPage;
    }

    public function isDisplayButtonOnListingPage(): bool
    {
        return $this->displayButtonOnListingPage;
    }

    public function setDisplayButtonOnListingPage(bool $displayButtonOnListingPage): void
    {
        $this->displayButtonOnListingPage = $displayButtonOnListingPage;
    }



    public function isDisplayButtonOnCheckoutRegisterPage(): bool
    {
        return $this->displayButtonOnCheckoutRegisterPage;
    }

    public function setDisplayButtonOnCheckoutRegisterPage(bool $displayButtonOnCheckoutRegisterPage): void
    {
        $this->displayButtonOnCheckoutRegisterPage = $displayButtonOnCheckoutRegisterPage;
    }

    public function isDisplayLoginButtonOnRegistrationPage(): bool
    {
        return $this->displayLoginButtonOnRegistrationPage;
    }

    public function setDisplayLoginButtonOnRegistrationPage(bool $displayLoginButtonOnRegistrationPage): void
    {
        $this->displayLoginButtonOnRegistrationPage = $displayLoginButtonOnRegistrationPage;
    }

    public function getButtonColor(): string
    {
        return $this->buttonColor;
    }

    public function setButtonColor(string $buttonColor): void
    {
        $this->buttonColor = $buttonColor;
    }

    public function getExcludedProductIds(): array
    {
        return $this->excludedProductIds;
    }

    public function setExcludedProductIds(array $excludedProductIds): void
    {
        $this->excludedProductIds = $excludedProductIds;
    }

    public function getExcludedProductStreamIds(): array
    {
        return $this->excludedProductStreamIds;
    }

    public function setExcludedProductStreamIds(array $excludedProductStreamIds): void
    {
        $this->excludedProductStreamIds = $excludedProductStreamIds;
    }

    public function setIsSandboxActive(bool $isSandbox): void
    {
        $this->sandboxActive = $isSandbox;
    }
}
