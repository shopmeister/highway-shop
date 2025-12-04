<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\Page\Extension;

class AmazonPayButtonExtension extends AbstractAmazonButtonExtension
{
    /**
     * The name of this extension. Use it to identify the correct extension.
     */
    public const EXTENSION_NAME = 'SwagAmazonPayButton';

    /**
     * Button placement types.
     */
    public const BUTTON_PLACEMENT_HOME = 'Home';
    public const BUTTON_PLACEMENT_PRODUCT = 'Product';
    public const BUTTON_PLACEMENT_CART = 'Cart';
    public const BUTTON_PLACEMENT_CHECKOUT = 'Checkout';
    public const BUTTON_PLACEMENT_OTHER = 'Other';
    public const PRODUCT_TYPE_PAY_AND_SHIP = 'PayAndShip';
    public const PRODUCT_TYPE_PAY_ONLY = 'PayOnly';

    public const VALID_PRODUCT_TYPES = [
        self::PRODUCT_TYPE_PAY_ONLY,
        self::PRODUCT_TYPE_PAY_AND_SHIP,
    ];

    protected string $addLineItemUrl;
    protected bool $isListingButtonEnabled = true;

    public function getAddLineItemUrl(): string
    {
        return $this->addLineItemUrl;
    }

    public function setAddLineItemUrl(string $addLineItemUrl): self
    {
        $this->addLineItemUrl = $addLineItemUrl;

        return $this;
    }

    public function isListingButtonEnabled(): bool
    {
        return $this->isListingButtonEnabled;
    }

    public function setIsListingButtonEnabled(bool $isListingButtonEnabled): self
    {
        $this->isListingButtonEnabled = $isListingButtonEnabled;

        return $this;
    }
}
