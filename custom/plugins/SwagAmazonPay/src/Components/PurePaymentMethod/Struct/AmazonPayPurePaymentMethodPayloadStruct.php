<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PurePaymentMethod\Struct;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\PurePaymentMethod\Exception\ActiveShippingAddressMissingException;

class AmazonPayPurePaymentMethodPayloadStruct implements \JsonSerializable
{
    use JsonSerializableTrait;

    protected ?string $checkoutResultReturnUrl = null;

    protected ?string $checkoutCancelUrl = null;

    protected string $storeId;

    protected CustomerEntity $customer;

    protected float $totalPrice;

    protected string $currencyCode;

    protected ?string $orderNumber = null;

    protected string $storeName;

    protected string $customInformation;

    protected bool $canHandlePendingAuth = false;

    public function isCanHandlePendingAuth(): bool
    {
        return $this->canHandlePendingAuth;
    }

    public function setCanHandlePendingAuth(bool $canHandlePendingAuth): AmazonPayPurePaymentMethodPayloadStruct
    {
        $this->canHandlePendingAuth = $canHandlePendingAuth;
        return $this;
    }

    public function getCheckoutResultReturnUrl(): ?string
    {
        return $this->checkoutResultReturnUrl;
    }

    public function setCheckoutResultReturnUrl(?string $checkoutResultReturnUrl): void
    {
        $this->checkoutResultReturnUrl = $checkoutResultReturnUrl;
    }

    public function setCheckoutCancelUrl(?string $checkoutCancelUrl): void
    {
        $this->checkoutCancelUrl = $checkoutCancelUrl;
    }

    public function getCheckoutCancelUrl(): ?string
    {
        return $this->checkoutCancelUrl;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getStoreName(): string
    {
        return $this->storeName;
    }

    public function setStoreName(string $storeName): void
    {
        $this->storeName = $storeName;
    }

    public function getCustomInformation(): string
    {
        return $this->customInformation;
    }

    public function setCustomInformation(string $customInformation): void
    {
        $this->customInformation = $customInformation;
    }

    public function jsonSerialize(): array
    {
        $shippingAddress = $this->customer->getActiveShippingAddress();
        if ($shippingAddress === null) {
            throw new ActiveShippingAddressMissingException($this->customer->getId());
        }

        return [
            'storeId' => $this->storeId,
            'webCheckoutDetails' => [
                'checkoutMode' => 'ProcessOrder',
                'checkoutResultReturnUrl' => $this->checkoutResultReturnUrl,
                'checkoutCancelUrl' => $this->checkoutCancelUrl,
            ],
            'paymentDetails' => [
                'paymentIntent' => 'Authorize',
                'canHandlePendingAuthorization' => $this->isCanHandlePendingAuth(),
                'chargeAmount' => [
                    'amount' => $this->totalPrice,
                    'currencyCode' => $this->currencyCode,
                ],
                'presentmentCurrency' => $this->currencyCode,
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $this->orderNumber,
                'merchantStoreName' => $this->storeName,
                'customInformation' => $this->customInformation,
            ],
            'addressDetails' => $this->getAddressDetails($shippingAddress),
            'platformId' => ConfigServiceInterface::PLATFORM_ID,
        ];
    }

    private function getAddressDetails(CustomerAddressEntity $shippingAddress): array
    {
        $addressDetails = [
            'name' => \sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName()),
            'addressLine1' => $shippingAddress->getStreet(),
            'city' => $shippingAddress->getCity(),
            'postalCode' => $shippingAddress->getZipcode(),
        ];

        if ($shippingAddress->getPhoneNumber() !== null) {
            $addressDetails['phoneNumber'] = $shippingAddress->getPhoneNumber();
        } else {
            // The phone number is mandatory for Amazon so for now a fallback is set until this changes.
            $addressDetails['phoneNumber'] = '0';
        }

        $country = $shippingAddress->getCountry();
        if ($country !== null) {
            $addressDetails['countryCode'] = $country->getIso();
        }

        $countryState = $shippingAddress->getCountryState();
        if ($countryState !== null) {
            $addressDetails['stateOrRegion'] = $countryState->getShortCode();
        }

        if ($shippingAddress->getAdditionalAddressLine1() !== null) {
            $addressDetails['addressLine2'] = $shippingAddress->getAdditionalAddressLine1();
        }

        if ($shippingAddress->getAdditionalAddressLine2() !== null) {
            $addressDetails['addressLine3'] = $shippingAddress->getAdditionalAddressLine2();
        }

        return $addressDetails;
    }
}
