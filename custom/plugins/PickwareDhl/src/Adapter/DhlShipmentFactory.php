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

use Pickware\DalBundle\EntityManager;
use Pickware\MoneyBundle\Currency;
use Pickware\MoneyBundle\CurrencyConverter;
use Pickware\MoneyBundle\CurrencyConverterException;
use Pickware\PickwareDhl\Api\ReturnShipmentOrder;
use Pickware\PickwareDhl\Api\Shipment;
use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\ShippingBundle\Shipment\Model\ShipmentDefinition;
use Pickware\ShippingBundle\Shipment\Model\ShipmentEntity;
use Pickware\ShippingBundle\Shipment\ShipmentBlueprint;
use Psr\Clock\ClockInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DhlShipmentFactory
{
    public function __construct(
        #[Autowire(service: 'Pickware\\MoneyBundle\\ShopwareCurrencyConverter')]
        private readonly CurrencyConverter $currencyConverter,
        private readonly EntityManager $entityManager,
        private readonly ClockInterface $clock,
    ) {}

    /**
     * @return Shipment[]
     */
    public function createShipmentsForShipment(
        string $shipmentId,
        DhlConfig $dhlConfig,
        Context $context,
    ): array {
        /** @var ShipmentEntity $shipment */
        $shipment = $this->entityManager->findByPrimaryKey(
            ShipmentDefinition::class,
            $shipmentId,
            $context,
        );
        if (!$shipment) {
            throw DhlAdapterException::shipmentNotFound($shipmentId);
        }
        $shipmentBlueprint = $shipment->getShipmentBlueprint();

        $dhlShipmentConfig = new DhlShipmentConfig($shipmentBlueprint->getShipmentConfig());
        $product = $dhlShipmentConfig->getProduct();
        $shipmentOrderOptions = $dhlShipmentConfig->getShipmentOrderOptions(
            dhlConfig: $dhlConfig,
            receiverAddress: $shipmentBlueprint->getReceiverAddress(),
        );

        if (count($shipmentBlueprint->getParcels()) === 0) {
            throw DhlAdapterException::shipmentBlueprintHasNoParcels();
        }

        $shipmentOrders = [];
        foreach ($shipmentBlueprint->getParcels() as $parcel) {
            $shipmentOrder = new Shipment(
                receiverAddress: $shipmentBlueprint->getReceiverAddress(),
                senderAddress: $shipmentBlueprint->getSenderAddress(),
                parcel: $parcel,
                product: $product,
                shipmentServices: $shipmentOrderOptions,
                totalFees: $shipmentBlueprint->getTotalFees()->multiply(1 / count($shipmentBlueprint->getParcels())),
                permitNumbers: $shipmentBlueprint->getPermitNumbers(),
                certificateNumbers: $shipmentBlueprint->getCertificateNumbers(),
                dhlBillingInformation: $dhlConfig->getBillingInformation(),
                shipmentDate: $this->clock->now(),
                typeOfShipment: $shipmentBlueprint->getTypeOfShipment(),
                officeOfOrigin: $shipmentBlueprint->getOfficeOfOrigin(),
                explanationIfTypeOfShipmentIsOther: $shipmentBlueprint->getExplanationIfTypeOfShipmentIsOther(),
                invoiceNumber: $shipmentBlueprint->getInvoiceNumber(),
                movementReferenceNumber: $shipmentBlueprint->getMovementReferenceNumber(),
            );

            $termsOfTrade = $dhlShipmentConfig->getTermsOfTrade();
            if ($termsOfTrade !== null) {
                $shipmentOrder->enableExportDocumentCreation($termsOfTrade);
            }
            $shipmentOrders[] = $shipmentOrder;
        }

        return $shipmentOrders;
    }

    /**
     * @return ReturnShipmentOrder[]
     */
    public function createReturnShipmentOrdersForShipment(
        ShipmentBlueprint $shipmentBlueprint,
        Context $context,
    ): array {
        $shipmentConfig = new DhlShipmentConfig($shipmentBlueprint->getShipmentConfig());
        $senderAddress = $shipmentBlueprint->getSenderAddress();

        if ($senderAddress->getCountry() === null) {
            throw DhlAdapterException::missingAddressProperty('sender', 'country');
        }

        if (count($shipmentBlueprint->getParcels()) === 0) {
            throw DhlAdapterException::shipmentBlueprintHasNoParcels();
        }

        $shipmentOrders = [];
        foreach ($shipmentBlueprint->getParcels() as $parcel) {
            $shipmentOrder = new ReturnShipmentOrder($senderAddress, $parcel);
            if ($shipmentConfig->getExportDocumentsActive()) {
                if (!$parcel->getTotalValue()) {
                    throw DhlAdapterException::customsInformationMissingTotalValue();
                }

                if (
                    !in_array(
                        $parcel->getTotalValue()->getCurrency()->getIsoCode(),
                        ReturnShipmentOrder::SUPPORTED_CURRENCY_CODES_FOR_CUSTOMS,
                    )
                ) {
                    try {
                        $shipmentBlueprint->convertAllMoneyValuesToSameCurrency($this->currencyConverter, new Currency('EUR'), $context);
                    } catch (CurrencyConverterException $e) {
                        throw DhlAdapterException::customsValuesCouldNotBeConverted($e);
                    }
                }

                $shipmentOrder->enableExportDocumentCreation();
            }

            $shipmentOrders[] = $shipmentOrder;
        }

        return $shipmentOrders;
    }
}
