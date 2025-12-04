<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Installation;

use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\PickwareDhl\ReturnLabel\ReturnLabelMailTemplate;
use Pickware\ShippingBundle\Carrier\Carrier;
use Pickware\ShippingBundle\ParcelPacking\ParcelPackingConfiguration;
use Pickware\UnitsOfMeasurement\PhysicalQuantity\Weight;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlCarrier extends Carrier
{
    public const TECHNICAL_NAME = 'dhl';

    public function __construct()
    {
        parent::__construct(
            technicalName: self::TECHNICAL_NAME,
            name: 'DHL',
            abbreviation: 'DHL',
            configDomain: DhlConfig::CONFIG_DOMAIN,
            shipmentConfigDescriptionFilePath: __DIR__ . '/../Resources/config/ShipmentConfigDescription.yaml',
            storefrontConfigDescriptionFilePath: __DIR__ . '/../Resources/config/StorefrontConfigDescription.yaml',
            returnShipmentConfigDescriptionFilePath: __DIR__ . '/../Resources/config/ReturnShipmentConfigDescription.yaml',
            defaultParcelPackingConfiguration: new ParcelPackingConfiguration(
                fallbackParcelWeight: null,
                maxParcelWeight: new Weight(31.5, 'kg'),
            ),
            returnLabelMailTemplateTechnicalName: ReturnLabelMailTemplate::TECHNICAL_NAME,
            batchSize: 10,
            supportsSenderAddressForShipments: true,
            supportsReceiverAddressForReturnShipments: false,
        );
    }
}
