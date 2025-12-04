<?php

declare(strict_types=1);


namespace Swag\AmazonPay\Storefront\Page\Extension;

use Shopware\Core\Framework\Struct\Struct;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;

class AbstractAmazonButtonExtension extends Struct
{
    /**
     * The name of this extension. Use it to identify the correct extension.
     */
    public const EXTENSION_NAME = 'SwagAmazonPayButton';

    /**
     * Button placement types
     */
    public const BUTTON_PLACEMENT_HOME = 'Home';
    public const BUTTON_PLACEMENT_PRODUCT = 'Product';
    public const BUTTON_PLACEMENT_CART = 'Cart';
    public const BUTTON_PLACEMENT_OTHER = 'Other';

    /**
     * Product types
     */
    public const PRODUCT_TYPE_PAY_AND_SHIP = 'PayAndShip';
    public const PRODUCT_TYPE_PAY_ONLY = 'PayOnly';


    /**
     * Valid types
     */

    public const VALID_PRODUCT_TYPES = [
        self::PRODUCT_TYPE_PAY_ONLY,
        self::PRODUCT_TYPE_PAY_AND_SHIP,
    ];

    public const JS_URL_EU = 'https://static-eu.payments-amazon.com/checkout.js';
    public const JS_URL_US = 'https://static-na.payments-amazon.com/checkout.js';

    protected string $merchantId;

    protected bool $sandbox = false;

    protected string $ledgerCurrency;

    protected string $checkoutLanguage;

    protected string $productType = self::PRODUCT_TYPE_PAY_AND_SHIP;

    protected string $placement = self::BUTTON_PLACEMENT_OTHER;

    protected string $libraryUrl = self::JS_URL_EU;
    protected bool $hideButton;

    protected bool $secureRequest = true;

    protected string $publicKeyId;

    protected string $payload = '';

    protected string $signature = '';

    protected string $buttonColor = 'Gold';

    protected bool $isShopware65 = false;

    protected ?array $estimatedOrderAmount = null;

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function setSandbox(bool $sandbox): void
    {
        $this->sandbox = $sandbox;
    }

    public function getLedgerCurrency(): string
    {
        return $this->ledgerCurrency;
    }

    public function setLedgerCurrency(string $ledgerCurrency): void
    {
        // Auto fallback to default value if an invalid value was passed.
        if (!\in_array($ledgerCurrency, AmazonPayConfigStruct::VALID_LEDGER_CURRENCIES, true)) {
            $ledgerCurrency = AmazonPayConfigStruct::LEDGER_CURRENCY_EU;
        }
        $this->ledgerCurrency = $ledgerCurrency;
        $this->libraryUrl = $this->ledgerCurrency === AmazonPayConfigStruct::LEDGER_CURRENCY_US ? self::JS_URL_US : self::JS_URL_EU;
    }

    public function getCheckoutLanguage(): string
    {
        return $this->checkoutLanguage;
    }

    public function setCheckoutLanguage(string $checkoutLanguage): void
    {
        $this->checkoutLanguage = $checkoutLanguage;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): void
    {
        // Auto fallback to default value if an invalid value was passed.
        if (!\in_array($productType, self::VALID_PRODUCT_TYPES, true)) {
            $this->productType = self::PRODUCT_TYPE_PAY_AND_SHIP;

            return;
        }

        $this->productType = $productType;
    }

    public function getPlacement(): string
    {
        return $this->placement;
    }

    public function setPlacement(string $placement): void
    {
        $this->placement = $placement;
    }

    public function getLibraryUrl(): string
    {
        return $this->libraryUrl;
    }

    public function setLibraryUrl(string $libraryUrl): void
    {
        $this->libraryUrl = $libraryUrl;
    }

    public function getHideButton(): bool
    {
        return $this->hideButton;
    }

    public function setHideButton(bool $hideButton): void
    {
        $this->hideButton = $hideButton;
    }

    public function isSecureRequest(): bool
    {
        return $this->secureRequest;
    }

    public function setSecureRequest(bool $secureRequest): void
    {
        $this->secureRequest = $secureRequest;
    }

    public function getPublicKeyId(): string
    {
        return $this->publicKeyId;
    }

    public function setPublicKeyId(string $publicKeyId): void
    {
        $this->publicKeyId = $publicKeyId;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getButtonColor(): string
    {
        return $this->buttonColor;
    }

    public function setButtonColor(string $buttonColor): void
    {
        $this->buttonColor = $buttonColor;
    }

    public function getEstimatedOrderAmount(): ?array
    {
        return $this->estimatedOrderAmount;
    }

    public function setEstimatedOrderAmount(?array $estimatedOrderAmount): void
    {
        $this->estimatedOrderAmount = $estimatedOrderAmount;
    }

    public function getIsShopware65(): bool
    {
        return $this->isShopware65;
    }

    public function setIsShopware65(bool $isShopware65): void
    {
        $this->isShopware65 = $isShopware65;
    }
}
