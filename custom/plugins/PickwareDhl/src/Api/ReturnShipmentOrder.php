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

use JsonSerializable;
use LogicException;
use Pickware\PickwareDhl\Adapter\DhlAdapterException;
use Pickware\ShippingBundle\Parcel\Parcel;
use Pickware\ShippingBundle\Shipment\Address;
use Pickware\ShippingBundle\Shipment\Country;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ReturnShipmentOrder implements JsonSerializable
{
    private ?string $receiverId = null;
    private bool $exportDocumentEnabled = false;

    public const SUPPORTED_CURRENCY_CODES_FOR_CUSTOMS = [
        'EUR',
        'USD',
        'CZK',
        'GBP',
        'CHF',
        'SGD',
    ];

    public function __construct(private readonly Address $shipperAddress, private readonly Parcel $parcel) {}

    public function jsonSerialize(): array
    {
        if (!$this->receiverId) {
            throw new LogicException('No recieverId set for ReturnShipmentOrder');
        }

        $shipmentOrder = [
            'customerReference' => $this->parcel->getCustomerReference(),
            'shipper' => self::getAddressAsShipperAddressArray($this->shipperAddress),
            'receiverId' => $this->receiverId,
        ];

        if ($this->parcel->getTotalWeight() !== null) {
            $shipmentOrder['itemWeight'] = [
                'uom' => 'kg',
                'value' => $this->parcel->getTotalWeight()->convertTo('kg'),
            ];
        }

        if ($this->exportDocumentEnabled) {
            $shipmentOrder['customsDetails'] = $this->createExportDocumentArray();
        }

        return $shipmentOrder;
    }

    public function getParcel(): Parcel
    {
        return $this->parcel;
    }

    public function getShipperAddress(): Address
    {
        return $this->shipperAddress;
    }

    public function setReceiverId(?string $receiverId): void
    {
        $this->receiverId = $receiverId;
    }

    public function enableExportDocumentCreation(): void
    {
        $this->exportDocumentEnabled = true;
    }

    private static function getAddressAsShipperAddressArray(Address $address): array
    {
        $names = $address->getOptimizedNameArray(keys: ['name1', 'name2', 'name3'], prioritizeCompanyIfSet: true);

        $country = new Country($address->getCountryIso());

        return array_merge($names, [
            'addressStreet' => $address->getStreet(),
            'addressHouse' => $address->getHouseNumber(),
            'postalCode' => $address->getZipCode(),
            'city' => $address->getCity(),
            'country' => $country->getIso3Code(),
            'phone' => $address->getPhone(),
            'email' => $address->getEmail(),
            'state' => $address->getStateIso(),
        ]);
    }

    private function createExportDocumentArray(): array
    {
        // Create export document
        $exportDocument = [
            'items' => [],
        ];

        foreach ($this->parcel->getItems() as $item) {
            $customsValue = $item->getUnitPrice();
            if ($customsValue === null) {
                throw DhlAdapterException::missingCustomsValueForParcelItem($item);
            }

            if (
                !in_array(
                    $customsValue->getCurrency()->getIsoCode(),
                    self::SUPPORTED_CURRENCY_CODES_FOR_CUSTOMS,
                )
            ) {
                throw DhlAdapterException::customsValueGivenInUnsupportedCurrency(
                    $item,
                    $customsValue->getCurrency(),
                );
            }

            if ($item->getUnitWeight() === null) {
                throw DhlAdapterException::missingWeightForParcelItem($item);
            }

            if ($item->getCountryOfOrigin() === null) {
                throw DhlAdapterException::missingCountryOfOriginForParcelItem($item);
            }

            $exportDocument['items'][] = [
                'itemDescription' => $item->getCustomsDescription(),
                'packagedQuantity' => $item->getQuantity(),
                'itemWeight' => [
                    'uom' => 'g',
                    'value' => $item->getUnitWeight()->convertTo('g'),
                ],
                'itemValue' => [
                    'currency' => $customsValue->getCurrency()->getIsoCode(),
                    'value' => round($customsValue->getValue(), 2),
                ],
                'countryOfOrigin' => mb_strtoupper($item->getCountryOfOrigin()->getIso3Code()),
                'hsCode' => $item->getTariffNumber(),
            ];
        }

        return $exportDocument;
    }
}
