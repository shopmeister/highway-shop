<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;
use Pickware\MoneyBundle\MoneyValue;
use Pickware\PickwareDhl\Adapter\DhlAdapterException;
use Pickware\PickwareDhl\Api\Services\AbstractShipmentOrderOption;
use Pickware\ShippingBundle\Parcel\Parcel;
use Pickware\ShippingBundle\Shipment\Address;
use Pickware\ShippingBundle\Shipment\ShipmentType;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class Shipment implements JsonSerializable
{
    private const WARENPOST_INTERNATIONAL_MAX_CUSTOMS_VALUE_IN_EUR = 1000;

    private bool $exportDocumentCreationEnabled = false;
    private ?Incoterm $termsOfTrade;
    private array $exportInformation;

    /**
     * @param AbstractShipmentOrderOption[] $shipmentServices
     */
    public function __construct(
        private readonly Address $receiverAddress,
        private readonly Address $senderAddress,
        private readonly Parcel $parcel,
        private readonly DhlProduct $product,
        private readonly array $shipmentServices,
        private readonly MoneyValue $totalFees,
        private readonly array $permitNumbers,
        private readonly array $certificateNumbers,
        private readonly DhlBillingInformation $dhlBillingInformation,
        private readonly DateTimeImmutable $shipmentDate,
        private readonly ?ShipmentType $typeOfShipment = null,
        private readonly ?string $officeOfOrigin = null,
        private readonly ?string $explanationIfTypeOfShipmentIsOther = null,
        private readonly ?string $invoiceNumber = null,
        private readonly ?string $movementReferenceNumber = null,
    ) {
        $parcelWeight = $this->parcel->getTotalWeight();
        if ($parcelWeight === null) {
            throw DhlAdapterException::parcelTotalWeightIsUndefined();
        }

        self::validateAddress('sender', $this->senderAddress);
        self::validateAddress('receiver', $this->receiverAddress);
    }

    public function jsonSerialize(): array
    {
        $shipment = [
            'product' => $this->product->getCode(),
            'billingNumber' => $this->dhlBillingInformation->getBillingNumberForProduct($this->product),
            'creationSoftware' => 'Pickware DHL',
            'shipDate' => $this->shipmentDate->setTimezone(new DateTimeZone('Europe/Berlin'))->format('Y-m-d'),
            'shipper' => self::getAddressAsShipperAddressArray($this->senderAddress),
            'consignee' => self::getAddressAsReceiverAddressArray($this->receiverAddress),
            'details' => [
                'dim' => [
                    'uom' => 'mm',
                    'height' => ceil($this->parcel->getDimensions()?->getHeight()->convertTo('mm') ?? 0.0),
                    'length' => ceil($this->parcel->getDimensions()?->getLength()->convertTo('mm') ?? 0.0),
                    'width' => ceil($this->parcel->getDimensions()?->getWidth()->convertTo('mm') ?? 0.0),
                ],
                'weight' => [
                    'uom' => 'kg',
                    'value' => round($this->parcel->getTotalWeight()->convertTo('kg'), precision: 3),
                ],
            ],
        ];

        if ($this->parcel->getCustomerReference() !== null) {
            // DHL requires the reference to be at least 8 characters. Since the reference
            // usually is the order number, it can easily be less than 8 characters. The
            // reference is then padded with #s.
            $shipment['refNo'] = str_pad($this->parcel->getCustomerReference(), 8, ' ', STR_PAD_RIGHT);
        }

        if ($this->exportDocumentCreationEnabled) {
            $shipment['customs'] = $this->exportInformation;
        }

        foreach ($this->shipmentServices as $shipmentService) {
            $shipmentService->applyToShipmentArray($shipment);
        }

        return $shipment;
    }

    private static function validateAddress(string $addressOwner, Address $address): void
    {
        if (self::isShipperRefAddress($address)) {
            return;
        }

        if (count($address->getOptimizedNameArray()) === 0) {
            throw DhlAdapterException::missingAddressProperty($addressOwner, 'name, company or address addition');
        }
        if ($address->getCountryIso() === '') {
            throw DhlAdapterException::missingAddressProperty($addressOwner, 'country');
        }
    }

    public function getParcel(): Parcel
    {
        return $this->parcel;
    }

    /**
     * @return AbstractShipmentOrderOption[]
     */
    public function getShipmentServices(): array
    {
        return $this->shipmentServices;
    }

    public function enableExportDocumentCreation(string $termsOfTrade): void
    {
        $this->exportDocumentCreationEnabled = true;
        $this->termsOfTrade = Incoterm::from($termsOfTrade);

        if (!$this->typeOfShipment) {
            throw DhlAdapterException::typeOfShipmentMissing();
        }

        if (
            $this->product->getCode() === DhlProduct::CODE_DHL_WARENPOST_INTERNATIONAL
            && $this->parcel->getTotalValue() !== null
            && $this->parcel->getTotalValue()->getValue() > self::WARENPOST_INTERNATIONAL_MAX_CUSTOMS_VALUE_IN_EUR
        ) {
            throw DhlAdapterException::orderRequiresExportDeclaration(self::WARENPOST_INTERNATIONAL_MAX_CUSTOMS_VALUE_IN_EUR);
        }

        $this->exportInformation = $this->generateExportPayload();
    }

    public function isExportDocumentCreationEnabled(): bool
    {
        return $this->exportDocumentCreationEnabled;
    }

    public function getTermsOfTrade(): Incoterm
    {
        return $this->termsOfTrade;
    }

    public function getShipmentDate(): DateTimeImmutable
    {
        return $this->shipmentDate;
    }

    private static function getAddressAsShipperAddressArray(Address $address): array
    {
        if (self::isShipperRefAddress($address)) {
            return [
                'shipperRef' => $address->getHouseNumber(),
            ];
        }

        $names = $address->getOptimizedNameArray(['name1', 'name2', 'name3'], prioritizeCompanyIfSet: true);

        $contactName = sprintf('%s %s', $address->getFirstName(), $address->getLastName());

        $addressArray = array_merge($names, [
            'addressStreet' => $address->getStreet(),
            'addressHouse' => $address->getHouseNumber(),
            'city' => $address->getCity(),
            'country' => mb_strtoupper($address->getCountry()->getIso3Code()),
        ]);

        if ($address->getZipCode() !== '') {
            $addressArray['postalCode'] = $address->getZipCode();
        }

        if (trim($contactName)) {
            $addressArray['contactName'] = $contactName;
        }

        if ($address->getEmail()) {
            $addressArray['email'] = $address->getEmail();
        }

        return $addressArray;
    }

    private static function getAddressAsReceiverAddressArray(Address $address): array
    {
        $names = $address->getOptimizedNameArray(['name1', 'name2', 'name3'], prioritizeCompanyIfSet: true);
        $contactName = sprintf('%s %s', $address->getFirstName(), $address->getLastName());

        $addressArray = array_merge($names, [
            'addressStreet' => $address->getStreet(),
            'addressHouse' => $address->getHouseNumber(),
            'city' => $address->getCity(),
            'country' => mb_strtoupper($address->getCountry()->getIso3Code()),
        ]);

        if ($address->getZipCode() !== '') {
            $addressArray['postalCode'] = $address->getZipCode();
        }

        if (trim($contactName)) {
            $addressArray['contactName'] = $contactName;
        }

        if ($address->getEmail()) {
            $addressArray['email'] = $address->getEmail();
        }

        if ($address->getPhone()) {
            $addressArray['phone'] = $address->getPhone();
        }

        return $addressArray;
    }

    private function generateExportPayload(): array
    {
        // Create export document
        $exportDocument = [
            'invoiceNo' => $this->invoiceNumber,
            'exportType' => $this->getExportTypeByShipmentType($this->typeOfShipment),
            'exportDescription' => $this->explanationIfTypeOfShipmentIsOther,
            'shippingConditions' => $this->termsOfTrade,
            'permitNo' => implode(',', $this->permitNumbers),
            'attestationNo' => implode(',', $this->certificateNumbers),
            'officeOfOrigin' => $this->officeOfOrigin,
            'shipperCustomsRef' => $this->senderAddress->getCustomsReference(),
            'consigneeCustomsRef' => $this->receiverAddress->getCustomsReference(),
            'items' => [],
        ];

        if ($this->movementReferenceNumber) {
            $exportDocument['MRN'] = $this->movementReferenceNumber;
        }

        foreach ($this->parcel->getItems() as $item) {
            $customsValue = $item->getUnitPrice();
            if ($customsValue === null) {
                throw DhlAdapterException::missingCustomsValueForParcelItem($item);
            }

            if ($item->getUnitWeight() === null) {
                throw DhlAdapterException::missingWeightForParcelItem($item);
            }

            if ($item->getCountryOfOrigin() === null) {
                throw DhlAdapterException::missingCountryOfOriginForParcelItem($item);
            }

            $exportDocument['items'][] = [
                'itemDescription' => $item->getCustomsDescription(),
                'countryOfOrigin' => mb_strtoupper($item->getCountryOfOrigin()->getIso3Code()),
                'hsCode' => $item->getTariffNumber(),
                'packagedQuantity' => $item->getQuantity(),
                'itemValue' => [
                    'currency' => $customsValue->getCurrency()->getIsoCode(),
                    'value' => round($customsValue->getValue(), 2),
                ],
                'itemWeight' => [
                    'uom' => 'kg',
                    // When rounding the weights of individual items in a parcel, it's possible for the total weight of the
                    // parcel to be less than the sum of each item's weight. For example, if one item weighs 4.5g and
                    // another weighs 9.5g, the sum is 14g. However, if the weights are rounded before adding them together,
                    // the sum would be 15g. DHL requires that the sum of the items' weights be less than or equal to the
                    // total weight of the parcel, or else the label creation process will fail. To avoid this issue, we use
                    // the floor() function to round down the weights of the individual items instead of rounding them.
                    // Unfortunately, floor() does not accept a $precision parameter, so we need to convert the weights to
                    // grams, round them down to the nearest gram, and then convert them back to kilograms.
                    'value' => floor($item->getUnitWeight()->convertTo('g')) / 1000,
                ],
            ];
        }

        if ($this->parcel->getTotalValue() === null) {
            throw DhlAdapterException::customsInformationMissingTotalValue();
        }

        // Postal charges is a required field for export documents but the value of the fees can be Zero.
        // If no Fees are currently set then the currency of the Fees is XXX but DHL uses the currency of the
        // postal charges for all money values on the export document. Because of this we are using the currency
        // of the total parcel value for the postal charges.
        $exportDocument['postalCharges'] = [
            'currency' => $this->parcel->getTotalValue()->getCurrency()->getIsoCode(),
            'value' => $this->totalFees->getValue(),
        ];

        return $exportDocument;
    }

    public function getReceiverAddress(): Address
    {
        return $this->receiverAddress;
    }

    public function getSenderAddress(): Address
    {
        return $this->senderAddress;
    }

    public function getProduct(): DhlProduct
    {
        return $this->product;
    }

    public function getBillingInformation(): DhlBillingInformation
    {
        return $this->dhlBillingInformation;
    }

    private function getExportTypeByShipmentType(ShipmentType $type): string
    {
        return match ($type) {
            ShipmentType::CommercialSample => 'COMMERCIAL_SAMPLE',
            ShipmentType::Documents => 'DOCUMENT',
            ShipmentType::Gift => 'PRESENT',
            ShipmentType::Other => 'OTHER',
            ShipmentType::ReturnedGoods => 'RETURN_OF_GOODS',
            ShipmentType::SaleOfGoods => 'COMMERCIAL_GOODS',
        };
    }

    private static function isShipperRefAddress(Address $address): bool
    {
        return $address->getStreet() === 'ShipperRef';
    }
}
