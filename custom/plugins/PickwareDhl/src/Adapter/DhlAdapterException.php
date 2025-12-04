<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Adapter;

use Pickware\ApiErrorHandlingBundle\JsonApiErrorTranslating\LocalizableJsonApiError;
use Pickware\MoneyBundle\Currency;
use Pickware\MoneyBundle\CurrencyConverterException;
use Pickware\PickwareDhl\DhlException;
use Pickware\ShippingBundle\Parcel\ParcelItem;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlAdapterException extends DhlException
{
    public static function customsValuesCouldNotBeConverted(
        CurrencyConverterException $currencyConverterException,
    ): self {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Customs values could not be converted',
                'de' => 'Zollwerte konnten nicht umgerechnet werden',
            ],
            'detail' => [
                'en' => sprintf(
                    'The DHL Parcel DE Returns API does support customs values in EUR, USD, CZK, GBP, CHF and SGD ' .
                    'only. At least one customs value of your shipment was not provided in one of ' .
                    'the supported currencies and could not be converted because of the following reason: %s',
                    $currencyConverterException->getMessage(),
                ),
                'de' => sprintf(
                    'Die DHL Parcel DE Returns API unterstützt nur Zollwerte in EUR, USD, CZK, GBP, CHF und SGD. ' .
                    'Mindestens ein Zollwert Ihrer Sendung war nicht in einer der unterstützten Währungen angegeben ' .
                    'und konnte aus folgendem Grund nicht umgerechnet werden: %s',
                    $currencyConverterException->getMessage(),
                ),
            ],
            'meta' => [
                'reason' => $currencyConverterException->getMessage(),
            ],
        ]), $currencyConverterException);
    }

    public static function invalidProductCode(string $productCode): self
    {
        if ($productCode === '') {
            return new self(new LocalizableJsonApiError([
                'title' => [
                    'en' => 'No DHL BCP product specified',
                    'de' => 'Kein DHL GKP Produkt angegeben',
                ],
                'detail' => [
                    'en' => 'No DHL BCP product was specified.',
                    'de' => 'Es wurde kein DHL GKP Produkt angegeben.',
                ],
            ]));
        }

        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Invalid DHL BCP product code',
                'de' => 'Ungültiger DHL GKP Produktcode',
            ],
            'detail' => [
                'en' => sprintf('The product code %s is not a valid DHL BCP product code.', $productCode),
                'de' => sprintf('Der Produktcode %s ist kein gültiger DHL GKP Produktcode.', $productCode),
            ],
            'meta' => [
                'productCode' => $productCode,
            ],
        ]));
    }

    public static function parcelConnectNoLongerAvailable(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Product no longer available',
                'de' => 'Produkt nicht mehr verfügbar',
            ],
            'detail' => [
                'en' => 'The product "Parcel Connect" is no longer available.',
                'de' => 'Das Produkt "Paket Connect" ist nicht mehr verfügbar.',
            ],
        ]));
    }

    public static function shipmentBlueprintHasNoParcels(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Shipment blueprint has no parcels',
                'de' => 'Sendungsentwurf enthält keine Pakete',
            ],
            'detail' => [
                'en' => 'The shipment has no parcels and therefore a label cannot be created.',
                'de' => 'Die Sendung enthält keine Pakete und daher kann kein Label erstellt werden.',
            ],
        ]));
    }

    public static function shipmentConfigIsMissingTermsOfTrade(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Shipment config is missing incoterm',
                'de' => 'Sendungskonfiguration fehlt Incoterm',
            ],
            'detail' => [
                'en' => 'It was requested to create export documents for the shipment but no incoterm was given in the ' .
                    'configuration.',
                'de' => 'Es wurde angefordert, Exportdokumente für die Sendung zu erstellen, aber kein Incoterm ' .
                    'wurde in der Konfiguration angegeben.',
            ],
        ]));
    }

    public static function shipmentConfigIsMissingDateOfBirthOrInWrongFormat(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Shipment config is missing date of birth',
                'de' => 'Geburtsdatum fehlt in Sendungskonfiguration',
            ],
            'detail' => [
                'en' => 'The date of birth is missing or in wrong format for service option Ident-Check.',
                'de' => 'Das Geburtsdatum fehlt oder ist im falschen Format für die Service-Option Ident-Check.',
            ],
        ]));
    }

    public static function shipmentNotFound(string $shipmentId): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Shipment not found',
                'de' => 'Sendung nicht gefunden',
            ],
            'detail' => [
                'en' => sprintf('The shipment with ID %s was not found.', $shipmentId),
                'de' => sprintf('Die Sendung mit der ID %s konnte nicht gefunden werden.', $shipmentId),
            ],
            'meta' => [
                'shipmentId' => $shipmentId,
            ],
        ]));
    }

    public static function orderRequiresExportDeclaration(float $maxCustomsValue): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Order requires export declaration',
                'de' => 'Bestellung erfordert Exportanmeldung',
            ],
            'detail' => [
                'en' => sprintf(
                    'This order requires an export declaration as it exceeds a customs value of %s€. Please use ' .
                    'the product DHL Paket International for this order.',
                    $maxCustomsValue,
                ),
                'de' => sprintf(
                    'Diese Bestellung erfordert eine Exportanmeldung, da sie einen Zollwert von %s€ übersteigt. ' .
                    'Bitte verwenden Sie das Produkt DHL Paket International für diese Bestellung.',
                    $maxCustomsValue,
                ),
            ],
            'meta' => [
                'maxCustomsValue' => $maxCustomsValue,
            ],
        ]));
    }

    public static function customsInformationMissingTotalValue(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Parcel is missing the total value for customs',
                'de' => 'Paket fehlt der Gesamtwert für den Zoll',
            ],
            'detail' => [
                'en' => 'At least one item in the parcel is missing a customs value and therefore the total parcel ' .
                    'value cannot be determined.',
                'de' => 'Mindestens ein Artikel im Paket hat keinen Zollwert und daher kann der Gesamtwert des ' .
                    'Pakets nicht bestimmt werden.',
            ],
        ]));
    }

    public static function parcelTotalWeightIsUndefined(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Parcel weight cannot be determined',
                'de' => 'Paketgewicht kann nicht ermittelt werden',
            ],
            'detail' => [
                'en' => 'The parcel has at least one item with an undefined weight. Set a weight for each item or ' .
                    'overwrite the total weight manually.',
                'de' => 'Das Paket enthält mindestens einen Artikel ohne Gewicht. Setze für jeden Artikel ein ' .
                    'Gewicht oder überschreibe das Gesamtgewicht manuell.',
            ],
        ]));
    }

    /**
     * @param string $addressOwner The owner of the address (i.e. 'receiver' or 'sender')
     */
    public static function missingAddressProperty(string $addressOwner, string $addressPropertyName): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Missing address property',
                'de' => 'Fehlendes Adressfeld',
            ],
            'detail' => [
                'en' => sprintf(
                    'The %s address is missing the following property: %s.',
                    $addressOwner,
                    ucfirst($addressPropertyName),
                ),
                'de' => sprintf(
                    'Das %s-Adressfeld enthält das folgende Feld nicht: %s.',
                    $addressOwner,
                    ucfirst($addressPropertyName),
                ),
            ],
            'meta' => [
                'addressOwner' => $addressOwner,
                'addressPropertyName' => $addressPropertyName,
            ],
        ]));
    }

    public static function missingCustomsValueForParcelItem(ParcelItem $item): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Missing customs information for parcel item',
                'de' => 'Zollinformationen fehlen für Paketartikel',
            ],
            'detail' => [
                'en' => sprintf(
                    'No customs value configured for item %s.',
                    $item->getName(),
                ),
                'de' => sprintf(
                    'Für den Artikel %s ist kein Zollwert konfiguriert.',
                    $item->getName(),
                ),
            ],
            'meta' => [
                'parcelItemName' => $item->getName(),
            ],
        ]));
    }

    public static function customsValueGivenInUnsupportedCurrency(ParcelItem $item, Currency $currency): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Unsupported currency for customs value',
                'de' => 'Nicht unterstützte Währung für Zollwert',
            ],
            'detail' => [
                'en' => sprintf(
                    'The customs value for parcel item "%s" is given in an unsupported currency "%s". ' .
                    'The DHL BCP currently supports EUR only.',
                    $item->getName(),
                    $currency->getIsoCode(),
                ),
                'de' => sprintf(
                    'Der Zollwert für den Paketartikel "%s" ist in einer nicht unterstützten Währung "%s" angegeben. ' .
                    'Das DHL GKP unterstützt derzeit nur EUR.',
                    $item->getName(),
                    $currency->getIsoCode(),
                ),
            ],
            'meta' => [
                'parcelItemName' => $item->getName(),
                'currencyIsoCode' => $currency->getIsoCode(),
            ],
        ]));
    }

    public static function missingWeightForParcelItem(ParcelItem $item): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Missing weight for parcel item',
                'de' => 'Gewicht fehlt für Paketartikel',
            ],
            'detail' => [
                'en' => sprintf(
                    'No weight configured for item %s.',
                    $item->getName(),
                ),
                'de' => sprintf(
                    'Für den Artikel %s ist kein Gewicht konfiguriert.',
                    $item->getName(),
                ),
            ],
            'meta' => [
                'parcelItemName' => $item->getName(),
            ],
        ]));
    }

    public static function missingCountryOfOriginForParcelItem(ParcelItem $item): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Missing country of origin for parcel item',
                'de' => 'Ursprungsland fehlt für Paketartikel',
            ],
            'detail' => [
                'en' => sprintf(
                    'No country of origin configured for item %s.',
                    $item->getName(),
                ),
                'de' => sprintf(
                    'Für den Artikel %s ist kein Ursprungsland konfiguriert.',
                    $item->getName(),
                ),
            ],
            'meta' => [
                'parcelItemName' => $item->getName(),
            ],
        ]));
    }

    public static function typeOfShipmentMissing(): self
    {
        return new self(new LocalizableJsonApiError([
            'title' => [
                'en' => 'Type of shipment missing',
                'de' => 'Art der Sendung fehlt',
            ],
            'detail' => [
                'en' => 'The type of the shipment is missing in the customs information for the shipment.',
                'de' => 'Die Art der Sendung fehlt in den Zollinformationen für die Sendung.',
            ],
        ]));
    }
}
