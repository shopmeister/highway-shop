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

use DateTime;
use DateTimeImmutable;
use Pickware\PickwareDhl\Api\DhlProduct;
use Pickware\PickwareDhl\Api\Services\AdditionalInsuranceServiceOption;
use Pickware\PickwareDhl\Api\Services\BulkyGoodsServiceOption;
use Pickware\PickwareDhl\Api\Services\CashOnDeliveryServiceOption;
use Pickware\PickwareDhl\Api\Services\ClosestDroppointDeliveryServiceOption;
use Pickware\PickwareDhl\Api\Services\EnclosedReturnLabelOption;
use Pickware\PickwareDhl\Api\Services\EndorsementServiceOption;
use Pickware\PickwareDhl\Api\Services\EndorsementType;
use Pickware\PickwareDhl\Api\Services\IdentCheckServiceOption;
use Pickware\PickwareDhl\Api\Services\MinimumAge;
use Pickware\PickwareDhl\Api\Services\NamedPersonOnlyServiceOption;
use Pickware\PickwareDhl\Api\Services\NoNeighbourDeliveryServiceOption;
use Pickware\PickwareDhl\Api\Services\ParcelOutletRoutingServiceOption;
use Pickware\PickwareDhl\Api\Services\PostalDeliveryDutyPaidServiceOption;
use Pickware\PickwareDhl\Api\Services\PreferredDayServiceOption;
use Pickware\PickwareDhl\Api\Services\PreferredLocationServiceOption;
use Pickware\PickwareDhl\Api\Services\PreferredNeighbourServiceOption;
use Pickware\PickwareDhl\Api\Services\PremiumServiceOption;
use Pickware\PickwareDhl\Api\Services\SignedForByRecipientServiceOption;
use Pickware\PickwareDhl\Api\Services\VisualCheckOfAgeServiceOption;
use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\ShippingBundle\Config\ConfigException;
use Pickware\ShippingBundle\Shipment\Address;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlShipmentConfig
{
    public function __construct(private readonly array $shipmentConfig) {}

    public function getShipmentOrderOptions(DhlConfig $dhlConfig, Address $receiverAddress): array
    {
        $shipmentOrderOptions = [];
        if (isset($this->shipmentConfig['bulkyGoods']) && $this->shipmentConfig['bulkyGoods']) {
            $shipmentOrderOptions[] = new BulkyGoodsServiceOption();
        }
        if (isset($this->shipmentConfig['enclosedReturnLabel']) && $this->shipmentConfig['enclosedReturnLabel']) {
            $shipmentOrderOptions[] = new EnclosedReturnLabelOption($dhlConfig->getBillingInformation());
        }
        if (isset($this->shipmentConfig['namedPersonOnly']) && $this->shipmentConfig['namedPersonOnly']) {
            $shipmentOrderOptions[] = new NamedPersonOnlyServiceOption();
        }
        if (
            isset($this->shipmentConfig['visualCheckOfAge'])
            && MinimumAge::tryFrom((int) ($this->shipmentConfig['visualCheckOfAge']))
        ) {
            $shipmentOrderOptions[] = new VisualCheckOfAgeServiceOption(
                MinimumAge::from((int) ($this->shipmentConfig['visualCheckOfAge'])),
            );
        }
        if (
            isset($this->shipmentConfig['additionalInsurance'])
            && is_numeric($this->shipmentConfig['additionalInsurance'])
            && (float) ($this->shipmentConfig['additionalInsurance']) > 0
        ) {
            $shipmentOrderOptions[] = new AdditionalInsuranceServiceOption(
                (float) ($this->shipmentConfig['additionalInsurance']),
            );
        }
        if (isset($this->shipmentConfig['codEnabled']) && $this->shipmentConfig['codEnabled']) {
            if (!isset($this->shipmentConfig['codAmount'])) {
                throw ConfigException::missingConfigurationField(DhlConfig::CONFIG_DOMAIN, 'codAmount');
            }

            $shipmentOrderOptions[] = new CashOnDeliveryServiceOption(
                $dhlConfig->getBankTransferData(),
                (float) $this->shipmentConfig['codAmount'],
                true,
            );
        }
        if (isset($this->shipmentConfig['identCheckEnabled']) && $this->shipmentConfig['identCheckEnabled']) {
            $dateOfBirth = $this->shipmentConfig['identCheckDateOfBirth'] ?? null;

            if (!empty($dateOfBirth)) {
                // | will reset all not required information to 0 which makes testing the date of birth easier
                // Further documentation: https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
                $dateOfBirth = DateTime::createFromFormat('Y-m-d|', $dateOfBirth);
                if (!$dateOfBirth) {
                    throw DhlAdapterException::shipmentConfigIsMissingDateOfBirthOrInWrongFormat();
                }
            }

            $shipmentOrderOptions[] = new IdentCheckServiceOption(
                $this->shipmentConfig['identCheckGivenName'],
                $this->shipmentConfig['identCheckSurname'],
                $dateOfBirth,
                MinimumAge::from((int) ($this->shipmentConfig['identCheckMinimumAge'])),
            );
        }
        if (isset($this->shipmentConfig['preferredDay']) && $this->shipmentConfig['preferredDay'] !== '') {
            // | will reset all not required information to 0 which makes testing the preferred day easier
            // Further documentation: https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
            $shipmentOrderOptions[] = new PreferredDayServiceOption(
                DateTimeImmutable::createFromFormat('Y-m-d|', $this->shipmentConfig['preferredDay']),
            );
        }
        if (isset($this->shipmentConfig['preferredNeighbour']) && $this->shipmentConfig['preferredNeighbour'] !== '') {
            $shipmentOrderOptions[] = new PreferredNeighbourServiceOption($this->shipmentConfig['preferredNeighbour']);
        }
        if (isset($this->shipmentConfig['preferredLocation']) && $this->shipmentConfig['preferredLocation'] !== '') {
            $shipmentOrderOptions[] = new PreferredLocationServiceOption($this->shipmentConfig['preferredLocation']);
        }
        if (isset($this->shipmentConfig['noNeighbourDelivery']) && $this->shipmentConfig['noNeighbourDelivery']) {
            $shipmentOrderOptions[] = new NoNeighbourDeliveryServiceOption();
        }
        if (isset($this->shipmentConfig['endorsement']) && $this->shipmentConfig['endorsement'] !== '') {
            $shipmentOrderOptions[] = new EndorsementServiceOption(EndorsementType::getFromConfig($this->shipmentConfig['endorsement']));
        }
        if (isset($this->shipmentConfig['parcelOutletRouting']) && $this->shipmentConfig['parcelOutletRouting']) {
            $shipmentOrderOptions[] = new ParcelOutletRoutingServiceOption($receiverAddress->getEmail());
        }
        if (isset($this->shipmentConfig['premium']) && $this->shipmentConfig['premium']) {
            $shipmentOrderOptions[] = new PremiumServiceOption();
        }
        if (isset($this->shipmentConfig['postalDeliveryDutyPaid']) && $this->shipmentConfig['postalDeliveryDutyPaid']) {
            $shipmentOrderOptions[] = new PostalDeliveryDutyPaidServiceOption();
        }
        if (isset($this->shipmentConfig['closestDroppointDelivery']) && $this->shipmentConfig['closestDroppointDelivery']) {
            $shipmentOrderOptions[] = new ClosestDroppointDeliveryServiceOption();
        }
        if (isset($this->shipmentConfig['signedForByRecipient']) && $this->shipmentConfig['signedForByRecipient']) {
            $shipmentOrderOptions[] = new SignedForByRecipientServiceOption();
        }

        return $shipmentOrderOptions;
    }

    public function getProduct(): DhlProduct
    {
        $productCode = $this->shipmentConfig['product'] ?? '';

        if ($productCode === 'V55PAK') {
            throw DhlAdapterException::parcelConnectNoLongerAvailable();
        }

        if (!DhlProduct::isValidProductCode($productCode)) {
            throw DhlAdapterException::invalidProductCode($productCode);
        }

        return DhlProduct::getByCode($productCode);
    }

    public function getTermsOfTrade(): ?string
    {
        if (isset($this->shipmentConfig['createExportDocuments']) && $this->shipmentConfig['createExportDocuments']) {
            if (!isset($this->shipmentConfig['incotermInternational']) && !isset($this->shipmentConfig['incotermEurope'])) {
                throw DhlAdapterException::shipmentConfigIsMissingTermsOfTrade();
            }

            return $this->shipmentConfig['incotermInternational'] ?? $this->shipmentConfig['incotermEurope'];
        }

        return null;
    }

    public function getExportDocumentsActive(): bool
    {
        return (bool) ($this->shipmentConfig['createExportDocuments'] ?? false);
    }

    public function mustEncode(): bool
    {
        return (bool) $this->shipmentConfig['printOnlyIfCodeable'];
    }
}
