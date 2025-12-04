<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Pay\Struct;

use Shopware\Core\Framework\Struct\JsonSerializableTrait;

class AmazonPayButtonPayloadStruct implements \JsonSerializable
{
    use JsonSerializableTrait;

    protected string $checkoutReviewReturnUrl;

    protected ?string $checkoutResultReturnUrl = null;

    protected string $storeId;

    protected string $currency = 'EUR';

    /**
     * @var array<string, array>
     */
    protected array $addressRestrictions;

    public function getCheckoutReviewReturnUrl(): string
    {
        return $this->checkoutReviewReturnUrl;
    }

    public function setCheckoutReviewReturnUrl(string $checkoutReviewReturnUrl): void
    {
        $this->checkoutReviewReturnUrl = $checkoutReviewReturnUrl;
    }

    public function getCheckoutResultReturnUrl(): ?string
    {
        return $this->checkoutResultReturnUrl;
    }

    public function setCheckoutResultReturnUrl(?string $checkoutResultReturnUrl): void
    {
        $this->checkoutResultReturnUrl = $checkoutResultReturnUrl;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getAddressRestrictions(): array
    {
        return $this->addressRestrictions;
    }

    public function setAddressRestrictions(array $addressRestrictions): void
    {
        $this->addressRestrictions = $addressRestrictions;
    }

    public function jsonSerialize(): array
    {
        $serializedData = [
            'storeId' => $this->storeId,
            'paymentDetails' => [
                'presentmentCurrency' => $this->currency,
            ],
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->checkoutReviewReturnUrl,
                'checkoutResultReturnUrl' => $this->checkoutResultReturnUrl,
            ],
        ];

        if (!empty($this->addressRestrictions)) {
            $serializedData['deliverySpecifications'] = [
                'addressRestrictions' => [
                    'type' => 'Allowed',
                    'restrictions' => $this->addressRestrictions,
                ],
            ];
        }

        return $serializedData;
    }
}
