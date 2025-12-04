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

use GuzzleHttp\Client;
use Pickware\DalBundle\EntityCollectionExtension;
use Pickware\DalBundle\EntityManager;
use Pickware\HttpUtils\JsonApi\JsonApiError;
use Pickware\MoneyBundle\MoneyValue;
use Pickware\PickwareDhl\Api\DhlApiClientException;
use Pickware\PickwareDhl\Api\DhlParcelApiClientFactory;
use Pickware\PickwareDhl\Api\DhlParcelDeSubSystem;
use Pickware\PickwareDhl\Api\Requests\CreateLabelsRequest;
use Pickware\PickwareDhl\Api\Requests\DeleteLabelsRequest;
use Pickware\PickwareDhl\Api\Shipment;
use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\PickwareDhl\FeatureFlag\DhlFeatureFlag;
use Pickware\PickwareDhl\Installation\DhlCarrier;
use Pickware\PickwareDhl\ReturnLabel\Request\CreateReturnLabelRequest;
use Pickware\ShippingBundle\Carrier\AbstractCarrierAdapter;
use Pickware\ShippingBundle\Carrier\Capabilities\CancellationCapability;
use Pickware\ShippingBundle\Carrier\Capabilities\CashOnDeliveryCapability;
use Pickware\ShippingBundle\Carrier\Capabilities\MultiTrackingCapability;
use Pickware\ShippingBundle\Carrier\Capabilities\ReturnShipmentsRegistrationCapability;
use Pickware\ShippingBundle\Carrier\CarrierAdapterRegistry;
use Pickware\ShippingBundle\Carrier\PageFormatProviding;
use Pickware\ShippingBundle\Config\Config;
use Pickware\ShippingBundle\Shipment\Model\ShipmentCollection;
use Pickware\ShippingBundle\Shipment\Model\ShipmentDefinition;
use Pickware\ShippingBundle\Shipment\Model\ShipmentEntity;
use Pickware\ShippingBundle\Shipment\Model\TrackingCodeDefinition;
use Pickware\ShippingBundle\Shipment\Model\TrackingCodeEntity;
use Pickware\ShippingBundle\Shipment\ShipmentsOperationResult;
use Pickware\ShippingBundle\Shipment\ShipmentsOperationResultSet;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    name: CarrierAdapterRegistry::CONTAINER_TAG,
    attributes: [
        'technicalName' => DhlCarrier::TECHNICAL_NAME,
        'featureFlagNames' => [DhlFeatureFlag::NAME],
    ],
)]
class DhlAdapter extends AbstractCarrierAdapter implements MultiTrackingCapability, CancellationCapability, ReturnShipmentsRegistrationCapability, CashOnDeliveryCapability, PageFormatProviding
{
    public const TRACKING_CODE_TYPE_SHIPMENT_NUMBER = 'shipmentNumber';
    public const TRACKING_CODE_TYPE_RETURN_SHIPMENT_NUMBER = 'returnShipmentNumber';

    public function __construct(
        private readonly DhlShipmentFactory $shipmentFactory,
        private readonly EntityManager $entityManager,
        private readonly DhlResponseProcessor $dhlResponseProcessor,
        private readonly DhlParcelApiClientFactory $dhlParcelApiClientFactory,
    ) {}

    /**
     * @param string[] $shipmentNumbers
     */
    public static function getTrackingUrlForShipmentNumbers(array $shipmentNumbers): string
    {
        return sprintf(
            'https://www.dhl.de/de/privatkunden/dhl-sendungsverfolgung.html?piececode=%s',
            implode(',', $shipmentNumbers),
        );
    }

    public function registerShipments(
        array $shipmentIds,
        Config $carrierConfig,
        Context $context,
    ): ShipmentsOperationResultSet {
        // Since DHL has "shipments" and the shipping bundle has "shipments", in the following code, the naming
        // PickwareShipment and DhlShipment is used to distinguish between the two.

        $dhlConfig = new DhlConfig($carrierConfig);

        $pickwareShipmentsOperationResultSet = new ShipmentsOperationResultSet();
        /** @var ShipmentCollection $pickwareShipments */
        $pickwareShipments = $this->entityManager->findBy(
            ShipmentDefinition::class,
            ['id' => $shipmentIds],
            $context,
        );

        $dhlParcelDeApiClient = $this->dhlParcelApiClientFactory->createParcelApiClient(
            $dhlConfig->getDhlParcelApiClientConfig(DhlParcelDeSubSystem::Parcels),
            DhlParcelDeSubSystem::Parcels,
            $context,
        );
        $pickwareShipmentDhlShipmentsMappingByEncodability = [
            'mustEncode' => [],
            'mustNotEncode' => [],
        ];
        foreach ($pickwareShipments as $pickwareShipment) {
            $operationDescription = sprintf(
                'Create label to %s %s',
                $pickwareShipment->getShipmentBlueprint()->getReceiverAddress()->getFirstName(),
                $pickwareShipment->getShipmentBlueprint()->getReceiverAddress()->getLastName(),
            );

            try {
                $dhlShipments = $this->shipmentFactory->createShipmentsForShipment(
                    shipmentId: $pickwareShipment->getId(),
                    dhlConfig: $dhlConfig,
                    context: $context,
                );
                $mustEncode = (new DhlShipmentConfig($pickwareShipment->getShipmentBlueprint()->getShipmentConfig()))->mustEncode();
                if ($mustEncode) {
                    $pickwareShipmentDhlShipmentsMappingByEncodability['mustEncode'][$pickwareShipment->getId()] = $dhlShipments;
                } else {
                    $pickwareShipmentDhlShipmentsMappingByEncodability['mustNotEncode'][$pickwareShipment->getId()] = $dhlShipments;
                }
            } catch (DhlAdapterException $e) {
                $pickwareShipmentsOperationResultSet->addShipmentOperationResult(
                    ShipmentsOperationResult::createFailedOperationResult(
                        [$pickwareShipment->getId()],
                        $operationDescription,
                        [$e->serializeToJsonApiError()],
                    ),
                );

                continue;
            }
        }

        $pickwareShipmentDhlShipmentCreationItemMappings = [
            ...$this->createLabels(
                dhlParcelDeApiClient: $dhlParcelDeApiClient,
                pickwareShipmentToDhlShipmentsMapping: $pickwareShipmentDhlShipmentsMappingByEncodability['mustEncode'],
                mustEncode: true,
                shipmentsOperationResultSet: $pickwareShipmentsOperationResultSet,
            ),
            ...$this->createLabels(
                dhlParcelDeApiClient: $dhlParcelDeApiClient,
                pickwareShipmentToDhlShipmentsMapping: $pickwareShipmentDhlShipmentsMappingByEncodability['mustNotEncode'],
                mustEncode: false,
                shipmentsOperationResultSet: $pickwareShipmentsOperationResultSet,
            ),
        ];

        $processingShipmentOperationResults = $this->dhlResponseProcessor->processCreateLabelsResponse(
            $pickwareShipmentDhlShipmentCreationItemMappings,
            context: $context,
        );

        array_map(
            $pickwareShipmentsOperationResultSet->addShipmentOperationResult(...),
            $processingShipmentOperationResults,
        );

        return $pickwareShipmentsOperationResultSet;
    }

    /**
     * @param array<string, array<Shipment>> $pickwareShipmentToDhlShipmentsMapping
     */
    private function createLabels(
        Client $dhlParcelDeApiClient,
        array $pickwareShipmentToDhlShipmentsMapping,
        bool $mustEncode,
        ShipmentsOperationResultSet $shipmentsOperationResultSet,
    ): array {
        if (count($pickwareShipmentToDhlShipmentsMapping) === 0) {
            return [];
        }
        try {
            $response = $dhlParcelDeApiClient->send(
                new CreateLabelsRequest(
                    shipments: array_merge(...array_values($pickwareShipmentToDhlShipmentsMapping)),
                    mustEncode: $mustEncode,
                ),
            );
            $responseRootObject = json_decode((string) $response->getBody(), flags: JSON_THROW_ON_ERROR);
            $items = $responseRootObject->items;

            $shipmentIdResponseItemMapping = [];
            foreach ($pickwareShipmentToDhlShipmentsMapping as $pickwareShipmentId => $dhlShipments) {
                foreach ($dhlShipments as $dhlShipment) {
                    $shipmentIdResponseItemMapping[] = new PickwareShipmentDhlShipmentCreationItemMapping(
                        pickwareShipmentId: $pickwareShipmentId,
                        dhlShipmentCreationItem: array_shift($items),
                    );
                }
            }

            return $shipmentIdResponseItemMapping;
        } catch (DhlApiClientException $e) {
            $shipmentsOperationResultSet->addShipmentOperationResult(
                ShipmentsOperationResult::createFailedOperationResult(
                    array_keys($pickwareShipmentToDhlShipmentsMapping),
                    $mustEncode ? 'Create encodable labels' : 'Create non-encodable labels',
                    [$e->serializeToJsonApiError()],
                ),
            );

            return [];
        }
    }

    public function generateTrackingUrlForTrackingCodes(array $trackingCodeIds, Context $context): string
    {
        $trackingCodes = $this->entityManager->findBy(
            TrackingCodeDefinition::class,
            ['id' => $trackingCodeIds],
            $context,
        );
        $shipmentNumbers = EntityCollectionExtension::getField($trackingCodes, 'trackingCode');

        return self::getTrackingUrlForShipmentNumbers($shipmentNumbers);
    }

    public function cancelShipments(
        array $shipmentIds,
        Config $carrierConfig,
        Context $context,
    ): ShipmentsOperationResultSet {
        /** @var ShipmentCollection $shipments */
        $shipments = $this->entityManager->findBy(
            ShipmentDefinition::class,
            ['id' => $shipmentIds],
            $context,
            ['trackingCodes'],
        );

        $shipmentsOperationResultSet = new ShipmentsOperationResultSet();

        $shipmentNumbers = [];
        $shipmentNumbersShipmentsMapping = [];
        foreach ($shipments as $shipment) {
            $numberOfCancellableTrackingCodesForShipment = 0;
            foreach ($shipment->getTrackingCodes() as $trackingCode) {
                $metaInformation = $trackingCode->getMetaInformation();
                if ($metaInformation['type'] !== self::TRACKING_CODE_TYPE_SHIPMENT_NUMBER) {
                    continue;
                }
                if (isset($metaInformation['cancelled']) && $metaInformation['cancelled']) {
                    $operationDescription = sprintf('Cancel label %s', $trackingCode->getTrackingCode());
                    $shipmentsOperationResult = ShipmentsOperationResult::createSuccessfulOperationResult(
                        EntityCollectionExtension::getField($shipments, 'id'),
                        $operationDescription,
                    );
                    $shipmentsOperationResultSet->addShipmentOperationResult($shipmentsOperationResult);

                    continue;
                }
                $shipmentNumbers[] = $trackingCode->getTrackingCode();
                $shipmentNumbersShipmentsMapping[$trackingCode->getTrackingCode()][] = $shipment->getId();
                $numberOfCancellableTrackingCodesForShipment++;
            }

            if ($numberOfCancellableTrackingCodesForShipment === 0) {
                $operationDescription = sprintf('Cancel shipment %s', $shipment->getId());
                $shipmentsOperationResult = ShipmentsOperationResult::createFailedOperationResult(
                    EntityCollectionExtension::getField($shipments, 'id'),
                    $operationDescription,
                    [
                        new JsonApiError([
                            'detail' => 'This shipment has no tracking codes that can be used to cancel the shipment',
                        ]),
                    ],
                );
                $shipmentsOperationResultSet->addShipmentOperationResult($shipmentsOperationResult);
            }
        }

        if (count($shipmentNumbers) === 0) {
            return $shipmentsOperationResultSet;
        }

        $dhlConfig = new DhlConfig($carrierConfig);
        $dhlApiClient = $this->dhlParcelApiClientFactory->createParcelApiClient(
            $dhlConfig->getDhlParcelApiClientConfig(DhlParcelDeSubSystem::Parcels),
            DhlParcelDeSubSystem::Parcels,
            $context,
        );

        $response = $dhlApiClient->send(new DeleteLabelsRequest($shipmentNumbers));

        $responseJson = json_decode((string) $response->getBody(), flags: JSON_THROW_ON_ERROR);

        $deletionItems = $responseJson->items;

        $trackingCodePayload = [];
        foreach ($deletionItems as $deletionItem) {
            $shipmentNumber = $deletionItem->shipmentNo;
            $operationDescription = sprintf('Cancel label %s', $shipmentNumber);

            $affectedShipmentIds = $shipmentNumbersShipmentsMapping[$shipmentNumber];
            $affectedShipments = $shipments->filter(fn(ShipmentEntity $shipment) => in_array($shipment->getId(), $affectedShipmentIds, true));

            if ($deletionItem->sstatus->status === 200) {
                $shipmentsOperationResult = ShipmentsOperationResult::createSuccessfulOperationResult(
                    EntityCollectionExtension::getField($affectedShipments, 'id'),
                    $operationDescription,
                );

                // Mark the tracking codes as cancelled
                foreach ($affectedShipments as $affectedShipment) {
                    $affectedTrackingCodes = $affectedShipment->getTrackingCodes()->filter(fn(TrackingCodeEntity $trackingCode) => $trackingCode->getTrackingCode() === $shipmentNumber);

                    foreach ($affectedTrackingCodes as $affectedTrackingCode) {
                        $metaInformation = $affectedTrackingCode->getMetaInformation();
                        $metaInformation['cancelled'] = true;
                        $trackingCodePayload[] = [
                            'id' => $affectedTrackingCode->getId(),
                            'metaInformation' => $metaInformation,
                        ];
                    }
                }
            } else {
                $shipmentsOperationResult = ShipmentsOperationResult::createFailedOperationResult(
                    EntityCollectionExtension::getField($affectedShipments, 'id'),
                    $operationDescription,
                    [new JsonApiError(['detail' => $deletionItem->sstatus->detail])],
                );
            }

            $shipmentsOperationResultSet->addShipmentOperationResult($shipmentsOperationResult);
        }

        if (count($trackingCodePayload) !== 0) {
            $this->entityManager->upsert(TrackingCodeDefinition::class, $trackingCodePayload, $context);
        }

        return $shipmentsOperationResultSet;
    }

    public function registerReturnShipments(
        array $shipmentIds,
        Config $carrierConfig,
        Context $context,
    ): ShipmentsOperationResultSet {
        /** @var ShipmentCollection $shipments */
        $shipments = $this->entityManager->findBy(ShipmentDefinition::class, ['id' => $shipmentIds], $context);
        $dhlConfig = new DhlConfig($carrierConfig);

        $parcelDeReturnApiClient = $this->dhlParcelApiClientFactory->createParcelApiClient(
            $dhlConfig->getDhlParcelApiClientConfig(DhlParcelDeSubSystem::Returns),
            DhlParcelDeSubSystem::Returns,
            $context,
        );

        $shipmentsOperationResultSet = new ShipmentsOperationResultSet();

        /** @var ShipmentEntity $shipment */
        foreach ($shipments as $shipment) {
            $operationDescription = sprintf(
                'Create return label for %s %s',
                $shipment->getShipmentBlueprint()->getSenderAddress()->getFirstName(),
                $shipment->getShipmentBlueprint()->getSenderAddress()->getLastName(),
            );
            if (!$shipment->getShipmentBlueprint()->getSenderAddress() || !$shipment->getShipmentBlueprint()->getSenderAddress()->getCountryIso()) {
                $operationResult = ShipmentsOperationResult::createFailedOperationResult(
                    [$shipment->getId()],
                    $operationDescription,
                    [new JsonApiError(['detail' => 'The sender address is missing the country.'])],
                );
                $shipmentsOperationResultSet->addShipmentOperationResult($operationResult);

                continue;
            }

            try {
                $shipmentOrders = $this->shipmentFactory->createReturnShipmentOrdersForShipment(
                    $shipment->getShipmentBlueprint(),
                    $context,
                );
            } catch (DhlApiClientException | DhlAdapterException $e) {
                $operationResult = ShipmentsOperationResult::createFailedOperationResult(
                    [$shipment->getId()],
                    $operationDescription,
                    [$e->serializeToJsonApiError()],
                );
                $shipmentsOperationResultSet->addShipmentOperationResult($operationResult);

                continue;
            }

            $receiverId = $dhlConfig->getReturnReceiverId(
                $shipment->getShipmentBlueprint()->getSenderAddress()->getCountry(),
            );

            foreach ($shipmentOrders as $shipmentOrder) {
                $shipmentOrder->setReceiverId($receiverId);
                try {
                    $request = new CreateReturnLabelRequest($shipmentOrder);
                    $response = $parcelDeReturnApiClient->send($request);
                } catch (DhlApiClientException | DhlAdapterException $e) {
                    $operationResult = ShipmentsOperationResult::createFailedOperationResult(
                        [$shipment->getId()],
                        $operationDescription,
                        [$e->serializeToJsonApiError()],
                    );
                    $shipmentsOperationResultSet->addShipmentOperationResult($operationResult);

                    continue;
                }

                $this->dhlResponseProcessor->processCreateReturnShipmentOrderResponse(
                    $response,
                    $shipment->getId(),
                    $shipmentOrder->getParcel()->getCustomerReference(),
                    $context,
                );
                $shipmentsOperationResultSet->addShipmentOperationResult(
                    ShipmentsOperationResult::createSuccessfulOperationResult(
                        [$shipment->getId()],
                        sprintf(
                            $operationDescription . ', parcel %s',
                            $shipmentOrder->getParcel()->getCustomerReference(),
                        ),
                    ),
                );
            }
        }

        return $shipmentsOperationResultSet;
    }

    public function enableCashOnDeliveryInShipmentConfig(array &$shipmentConfig, MoneyValue $amount): void
    {
        $shipmentConfig['codAmount'] = $amount->getValue();

        $shipmentConfig['codEnabled'] = true;
    }

    public function isCashOnDeliveryEnabledInShipmentConfig(array $shipmentConfig): bool
    {
        return $shipmentConfig['codEnabled'] ?? false;
    }

    public function getPageFormats(): array
    {
        return DhlLabelSize::getSupportedPageFormats();
    }
}
