<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;

abstract class AbstractOrderDocumentResolver implements OrderDocumentResolverInterface, TypeResolverInterface
{
    private $customFieldSetRepository;

    public function __construct($customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function getAssociations(string $type): array
    {
        return [
            'order' => [
                'deliveries.shippingCosts',
                'lineItems',
                'lineItems.payload',
                'lineItems.cover', // Added via event enricher
                'lineItems.product',
                'lineItems.product.unit',
                'lineItems.product.visibilities',
                'lineItems.product.deliveryTime',
                'deliveries.shippingMethod',
                'deliveries.shippingCosts',
                'deliveries.shippingOrderAddress.country',
                'deliveries.shippingOrderAddress.countryState',
                'cartPrice.calculatedTaxes',
                'transactions.paymentMethod',
                'currency',
                'addresses.country',
                'addresses.countryState',
                'language',
                'language.locale',
                'orderCustomer',
                'orderCustomer.customer',
                'orderCustomer.salutation',
                'documents',
                'documents.documentType'
            ],
        ];
    }

    public function getAdditionalDataTypes(string $type, Context $context): array
    {
        $additionalDataTypes = [];
        $criteria = new Criteria();
        $criteria->addAssociation('customFields');
        $criteria->addAssociation('relations');
        $customFieldSets = $this->customFieldSetRepository->search($criteria, $context);
        /** @var CustomFieldSetEntity $customFieldSet */
        foreach ($customFieldSets as $customFieldSet) {
            /** @var CustomFieldSetRelationEntity $relation */
            foreach ($customFieldSet->getRelations() as $relation) {

                if ($relation->getEntityName() === "product") {

                    if (!isset($additionalDataTypes['order'])) {
                        $additionalDataTypes['order'] = [
                            "type" => "object",
                            "data" => []
                        ];
                    }

                    if (!isset($additionalDataTypes['order']['data']['lineItems.last'])) {
                        $additionalDataTypes['order']['data']['lineItems.last'] = [
                            "type" => "object",
                            "data" => [
                                "product" => [
                                    "type" => "object",
                                    "data" => [
                                        "translated" => [
                                            "type" => "object",
                                            "data" => [
                                                "customFields" => [
                                                    "type" => "object",
                                                    "data" => []
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }

                    /** @var CustomFieldEntity $customField */
                    foreach ($customFieldSet->getCustomFields() as $customField) {
                        if (!isset($additionalDataTypes['order']['data']['lineItems.last']['data']['product']['data']['translated']['data']['customFields']['data'][$customField->getName()])) {
                            $additionalDataTypes['order']['data']['lineItems.last']['data']['product']['data']['translated']['data']['customFields']['data'][$customField->getName()] = [
                                "type" => $customField->getType()
                            ];
                        }
                    }

                } elseif ($relation->getEntityName() === "order") {

                    if (!isset($additionalDataTypes['order'])) {
                        $additionalDataTypes['order'] = [
                            "type" => "object",
                            "data" => []
                        ];
                    }

                    if (!isset($additionalDataTypes['order']['data']['customFields'])) {
                        $additionalDataTypes['order']['data']['customFields'] = [
                            "type" => "object",
                            "data" => []
                        ];
                    }

                    /** @var CustomFieldEntity $customField */
                    foreach ($customFieldSet->getCustomFields() as $customField) {
                        if (!isset($additionalDataTypes['order']['data']['customFields']['data'][$customField->getName()])) {
                            $additionalDataTypes['order']['data']['customFields']['data'][$customField->getName()] = [
                                "type" => $customField->getType()
                            ];
                        }
                    }

                } elseif ($relation->getEntityName() === "customer") {

                    if (!isset($additionalDataTypes['order'])) {
                        $additionalDataTypes['order'] = [
                            "type" => "object",
                            "data" => []
                        ];
                    }

                    if (!isset($additionalDataTypes['order']['data']['orderCustomer'])) {
                        $additionalDataTypes['order']['data']['orderCustomer'] = [
                            "type" => "object",
                            "data" => [
                                "customer" => [
                                    "type" => "object",
                                    "data" => [
                                        "customFields" => [
                                            "type" => "object",
                                            "data" => []
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }

                    /** @var CustomFieldEntity $customField */
                    foreach ($customFieldSet->getCustomFields() as $customField) {
                        if (!isset($additionalDataTypes['order']['data']['orderCustomer']['data']['customer']['data']['customFields']['data'][$customField->getName()])) {
                            $additionalDataTypes['order']['data']['orderCustomer']['data']['customer']['data']['customFields']['data'][$customField->getName()] = [
                                "type" => $customField->getType()
                            ];
                        }
                    }

                }

            }
        }
        return $additionalDataTypes;
    }

}
