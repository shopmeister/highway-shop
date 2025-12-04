<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Services;

use Pickware\PickwareDhl\Api\DhlBankTransferData;

class CashOnDeliveryServiceOption extends AbstractShipmentOrderOption
{
    /**
     * @param bool $addFee Configuration whether the transmission fee to be added to the COD amount or not by DHL.
     *     Select the option then the new COD amount will automatically printed on the shipping label and will
     *     transferred to the end of the day to DHL. Do not select the option and the specified COD amount remains
     *     unchanged.
     */
    public function __construct(
        private readonly DhlBankTransferData $bankTransferData,
        private readonly float $amountInEuro,
        private readonly bool $addFee,
    ) {}

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['cashOnDelivery'] = [
            'amount' => [
                'currency' => 'EUR',
                'value' => round($this->amountInEuro, 2),
            ],
        ];

        $shipmentOrderArray['services']['cashOnDelivery'] = array_merge(
            $shipmentOrderArray['services']['cashOnDelivery'],
            $this->bankTransferData->getAsArrayForShipmentDetails(
                $shipmentOrderArray['refNo'],
            ),
        );
    }
}
