<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Address;

use ShopmasterZalandoConnectorSix\Struct\Collection;

class ImportOrderAddressCollection extends Collection
{
    /**
     * @var ImportOrderAddressStruct
     */
    private ImportOrderAddressStruct $billingAddress;
    /**
     * @var ImportOrderAddressStruct
     */
    private ImportOrderAddressStruct $shippingAddress;

    /**
     * @return ImportOrderAddressStruct
     */
    public function getBillingAddress(): ImportOrderAddressStruct
    {
        return $this->billingAddress;
    }

    /**
     * @return ImportOrderAddressStruct
     */
    public function getShippingAddress(): ImportOrderAddressStruct
    {
        return $this->shippingAddress;
    }

    /**
     * @param ImportOrderAddressStruct $shippingAddress
     * @return self
     */
    public function setShippingAddress(ImportOrderAddressStruct $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    protected function getExpectedClass(): ?string
    {
        return ImportOrderAddressStruct::class;
    }

    /**
     * @return string
     */
    public function getBillingAddressId(): string
    {
        return $this->billingAddress->getId();
    }


    /**
     * @param ImportOrderAddressStruct $address
     * @return void
     */
    public function setBillingAddress(ImportOrderAddressStruct $address)
    {
        $this->billingAddress = $address;
    }
}