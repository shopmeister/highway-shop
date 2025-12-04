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
use Pickware\DocumentBundle\Document\DocumentContentsService;
use Pickware\DocumentBundle\Document\Model\DocumentEntity;
use Pickware\DocumentBundle\Document\PageFormat;
use Pickware\HttpUtils\JsonApi\JsonApiError;
use Pickware\PickwareDhl\DhlException;
use Pickware\ShippingBundle\Installation\Documents\CustomsDeclarationDocumentType;
use Pickware\ShippingBundle\Installation\Documents\ReturnLabelDocumentType;
use Pickware\ShippingBundle\Installation\Documents\ShippingLabelDocumentType;
use Pickware\ShippingBundle\Shipment\Model\ShipmentCollection;
use Pickware\ShippingBundle\Shipment\Model\ShipmentDefinition;
use Pickware\ShippingBundle\Shipment\Model\ShipmentEntity;
use Pickware\ShippingBundle\Shipment\Model\ShippingDirection;
use Pickware\ShippingBundle\Shipment\Model\TrackingCodeDefinition;
use Pickware\ShippingBundle\Shipment\ShipmentsOperationResult;
use Psr\Http\Message\ResponseInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use stdClass;

class DhlResponseProcessor
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly DocumentContentsService $documentContentsService,
    ) {}

    /**
     * @param PickwareShipmentDhlShipmentCreationItemMapping[] $pickwareShipmentDhlShipmentCreationItemMappings
     * @return ShipmentsOperationResult[]
     */
    public function processCreateLabelsResponse(
        array $pickwareShipmentDhlShipmentCreationItemMappings,
        Context $context,
    ): array {
        $pickwareShipmentIds = array_values(array_unique(array_map(
            fn(PickwareShipmentDhlShipmentCreationItemMapping $mapping) => $mapping->getPickwareShipmentId(),
            $pickwareShipmentDhlShipmentCreationItemMappings,
        )));

        /** @var ShipmentCollection $pickwareShipments */
        $pickwareShipments = $this->entityManager->findBy(
            ShipmentDefinition::class,
            ['id' => $pickwareShipmentIds],
            $context,
        );

        $pickwareShipmentOperationResults = [];
        $pickwareShipmentId = null;
        $parcelIndex = 0;
        foreach ($pickwareShipmentDhlShipmentCreationItemMappings as $mapping) {
            if ($pickwareShipmentId !== $mapping->getPickwareShipmentId()) {
                $pickwareShipmentId = $mapping->getPickwareShipmentId();
                $parcelIndex = 0;
            } else {
                $parcelIndex += 1;
            }
            $dhlShipmentCreationItem = $mapping->getDhlShipmentCreationItem();
            /** @var ShipmentEntity $pickwareShipment */
            $pickwareShipment = $pickwareShipments->get($pickwareShipmentId);

            $operationDescription = sprintf(
                'Create label to %s %s, parcel %d',
                $pickwareShipment->getShipmentBlueprint()->getReceiverAddress()->getFirstName(),
                $pickwareShipment->getShipmentBlueprint()->getReceiverAddress()->getLastName(),
                $parcelIndex + 1,
            );

            if ($dhlShipmentCreationItem->sstatus->status !== 200) {
                $errorMessages = array_values(array_unique(array_map(
                    fn(stdClass $validationMessage) => $validationMessage->validationMessage,
                    $dhlShipmentCreationItem->validationMessages,
                )));

                $errors = array_map(
                    fn(string $errorMessage) => new JsonApiError(['detail' => $errorMessage]),
                    $errorMessages,
                );

                $lastError = array_pop($errors);
                $lastError = DhlException::addHelpcenterLinkToError($lastError);
                $errors[] = $lastError;

                $pickwareShipmentsOperationResult = ShipmentsOperationResult::createFailedOperationResult(
                    [$pickwareShipmentId],
                    $operationDescription,
                    $errors,
                );
            } else {
                $pickwareShipmentsOperationResult = ShipmentsOperationResult::createSuccessfulOperationResult(
                    [$pickwareShipmentId],
                    $operationDescription,
                );
            }

            $pickwareShipmentOperationResults[] = $pickwareShipmentsOperationResult;
            if (!$pickwareShipmentsOperationResult->isSuccessful()) {
                continue;
            }

            $customerReference = $pickwareShipment->getShipmentBlueprint()->getParcels()[$parcelIndex]->getCustomerReference();
            $labelDocumentFileNameSuffix = $customerReference ? sprintf('-%s', $customerReference) : '';
            $this->createLabelDocument($pickwareShipment, $dhlShipmentCreationItem, $labelDocumentFileNameSuffix, $context);

            // Save return label document to database if one is contained in the request
            if (isset($dhlShipmentCreationItem->returnLabel)) {
                $this->createReturnLabelDocument($pickwareShipment, $dhlShipmentCreationItem, $labelDocumentFileNameSuffix, $context);
            }

            // Save export documents to database if one is contained in the request
            if (isset($dhlShipmentCreationItem->customsDoc)) {
                $this->createExportLabelDocument($pickwareShipment, $dhlShipmentCreationItem, $labelDocumentFileNameSuffix, $context);
            }
        }

        return $pickwareShipmentOperationResults;
    }

    public function processCreateReturnShipmentOrderResponse(
        ResponseInterface $response,
        string $shipmentId,
        string $customerReference,
        Context $context,
    ): void {
        $responseJson = json_decode((string)$response->getBody(), flags: JSON_THROW_ON_ERROR);

        $labelDocumentFileNameSuffix = $customerReference ? sprintf('-%s', $customerReference) : '';

        if (isset($responseJson->label)) {
            $returnDocumentId = $this->documentContentsService->saveStringAsDocument(
                base64_decode($responseJson->label->b64),
                $context,
                [
                    'fileName' => sprintf('return-label-dhl%s.pdf', $labelDocumentFileNameSuffix),
                    'mimeType' => 'application/pdf',
                    'orientation' => DocumentEntity::ORIENTATION_PORTRAIT,
                    'documentTypeTechnicalName' => ReturnLabelDocumentType::TECHNICAL_NAME,
                    'pageFormat' => DhlLabelSize::A4SelfprintReturn->getPageFormat(),
                    'extensions' => [
                        'pickwareShippingShipments' => [
                            [
                                'id' => $shipmentId,
                            ],
                        ],
                    ],
                ],
            );

            $returnShipmentNumber = $responseJson->shipmentNo;
            $trackingCodePayload = [
                'id' => Uuid::randomHex(),
                'trackingCode' => $returnShipmentNumber,
                'trackingUrl' => DhlAdapter::getTrackingUrlForShipmentNumbers([$returnShipmentNumber]),
                'metaInformation' => [
                    'type' => DhlAdapter::TRACKING_CODE_TYPE_RETURN_SHIPMENT_NUMBER,
                ],
                'shipmentId' => $shipmentId,
                'shippingDirection' => ShippingDirection::Incoming,
                'documents' => [
                    [
                        'id' => $returnDocumentId,
                    ],
                ],
            ];

            $this->entityManager->create(TrackingCodeDefinition::class, [$trackingCodePayload], $context);
        }
    }

    private function createShipmentsOperationResult(
        $creationState,
        ShipmentEntity $shipment,
        $parcelIndex,
    ): ShipmentsOperationResult {
        $operationDescription = sprintf(
            'Create label to %s %s, parcel %d',
            $shipment->getShipmentBlueprint()->getReceiverAddress()->getFirstName(),
            $shipment->getShipmentBlueprint()->getReceiverAddress()->getLastName(),
            $parcelIndex + 1,
        );

        if ($creationState->LabelData->Status->statusCode !== 0) {
            $errorMessages = array_values(array_unique([
                $creationState->LabelData->Status->statusText,
                ...$creationState->LabelData->Status->statusMessage,
            ]));

            $errors = array_map(
                fn(string $errorMessage) => new JsonApiError(['detail' => $errorMessage]),
                $errorMessages,
            );

            $lastError = array_pop($errors);
            $lastError = DhlException::addHelpcenterLinkToError($lastError);
            $errors[] = $lastError;

            return ShipmentsOperationResult::createFailedOperationResult(
                [$shipment->getId()],
                $operationDescription,
                $errors,
            );
        }

        return ShipmentsOperationResult::createSuccessfulOperationResult(
            [$shipment->getId()],
            $operationDescription,
        );
    }

    private function createLabelDocument(ShipmentEntity $shipment, stdClass $shipmentData, string $labelDocumentFileNameSuffix, Context $context): void
    {
        $shipmentNumber = $shipmentData->shipmentNo;

        $documentId = $this->documentContentsService->saveStringAsDocument(
            base64_decode($shipmentData->label->b64),
            $context,
            [
                'fileName' => sprintf('shipping-label-dhl%s.pdf', $labelDocumentFileNameSuffix),
                'mimeType' => 'application/pdf',
                'orientation' => DocumentEntity::ORIENTATION_PORTRAIT,
                'documentTypeTechnicalName' => ShippingLabelDocumentType::TECHNICAL_NAME,
                'pageFormat' => DhlLabelSize::A5->getPageFormat(),
                'extensions' => [
                    'pickwareShippingShipments' => [
                        [
                            'id' => $shipment->getId(),
                        ],
                    ],
                ],
            ],
        );

        $trackingCodesPayload = [
            'id' => Uuid::randomHex(),
            'trackingCode' => $shipmentNumber,
            'trackingUrl' => DhlAdapter::getTrackingUrlForShipmentNumbers([$shipmentNumber]),
            'metaInformation' => [
                'type' => DhlAdapter::TRACKING_CODE_TYPE_SHIPMENT_NUMBER,
            ],
            'shipmentId' => $shipment->getId(),
            'shippingDirection' => ShippingDirection::Outgoing,
            'documents' => [
                [
                    'id' => $documentId,
                ],
            ],
        ];
        $this->entityManager->create(TrackingCodeDefinition::class, [$trackingCodesPayload], $context);
    }

    private function createReturnLabelDocument(ShipmentEntity $shipment, stdClass $shipmentData, string $labelDocumentFileNameSuffix, Context $context): void
    {
        $returnShipmentNumber = $shipmentData->returnShipmentNo ?? null;

        $returnDocumentId = $this->documentContentsService->saveStringAsDocument(
            base64_decode($shipmentData->returnLabel->b64),
            $context,
            [
                'fileName' => sprintf('return-label-dhl%s.pdf', $labelDocumentFileNameSuffix),
                'mimeType' => 'application/pdf',
                'orientation' => DocumentEntity::ORIENTATION_PORTRAIT,
                'documentTypeTechnicalName' => ReturnLabelDocumentType::TECHNICAL_NAME,
                'pageFormat' => DhlLabelSize::A5->getPageFormat(),
                'extensions' => [
                    'pickwareShippingShipments' => [
                        [
                            'id' => $shipment->getId(),
                        ],
                    ],
                ],
            ],
        );

        if ($returnShipmentNumber) {
            $trackingCodePayload = [
                'id' => Uuid::randomHex(),
                'trackingCode' => $returnShipmentNumber,
                'trackingUrl' => DhlAdapter::getTrackingUrlForShipmentNumbers([$returnShipmentNumber]),
                'metaInformation' => [
                    'type' => DhlAdapter::TRACKING_CODE_TYPE_RETURN_SHIPMENT_NUMBER,
                ],
                'shippingDirection' => ShippingDirection::Incoming,
                'shipmentId' => $shipment->getId(),
                'documents' => [
                    [
                        'id' => $returnDocumentId,
                    ],
                ],
            ];
            $this->entityManager->create(TrackingCodeDefinition::class, [$trackingCodePayload], $context);
        }
    }

    private function createExportLabelDocument(ShipmentEntity $shipment, stdClass $shipmentData, string $labelDocumentFileNameSuffix, Context $context): void
    {
        $this->documentContentsService->saveStringAsDocument(
            base64_decode($shipmentData->customsDoc->b64),
            $context,
            [
                'fileName' => sprintf('export-document-dhl%s.pdf', $labelDocumentFileNameSuffix),
                'mimeType' => 'application/pdf',
                'orientation' => DocumentEntity::ORIENTATION_PORTRAIT,
                'documentTypeTechnicalName' => CustomsDeclarationDocumentType::TECHNICAL_NAME,
                'pageFormat' => PageFormat::createDinPageFormat('A4'),
                'extensions' => [
                    'pickwareShippingShipments' => [
                        [
                            'id' => $shipment->getId(),
                        ],
                    ],
                ],
            ],
        );
    }
}
