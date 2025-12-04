<?php

namespace ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\CustomFieldsInterface;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class OrderCustomFields implements CustomFieldsInterface
{

    const CUSTOM_FIELD_ORDERNUMBER = 'sm_orderNumber';
    const CUSTOM_FIELD_CUSTOMERNUMBER = 'sm_customerNumber';
    const CUSTOM_FIELD_RETURN_TRACKING_NUMBER = 'sm_returnTrackingNumber';
    const CUSTOM_FIELD_SALES_CHANNEL_ID = 'sm_salesChannelId';
    const CUSTOM_FIELD_ORDER_ID = 'sm_orderId';
    const CUSTOM_FIELD_STATUS_SENT = 'sm_sentStatus';

    public static function getSetId(): string
    {
        return '3f307b7a4204e9e749929ce3c988c601';
    }

    public static function getSet(): array
    {
        return [
            'id' => self::getSetId(),
            'name' => self::ORDER_ADDITIONAL_SET_NAME,
            'active' => true,
            'config' => [
                'label' => [
                    'de-DE' => 'Zalando Orders',
                    'en-GB' => 'Zalando Orders'
                ]
            ],
            'relations' => [
                ['entityName' => OrderDefinition::ENTITY_NAME]
            ],
            'customFields' => [
                [
                    'id' => md5(self::CUSTOM_FIELD_ORDERNUMBER),
                    'name' => self::CUSTOM_FIELD_ORDERNUMBER,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 0,
                        'componentName' => 'sw-field',
                        'disabled' => true,
                        'numberType' => CustomFieldTypes::TEXT,
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando order number',
                            'en-GB' => 'Zalando order number',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_CUSTOMERNUMBER),
                    'name' => self::CUSTOM_FIELD_CUSTOMERNUMBER,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 1,
                        'componentName' => 'sw-field',
                        'disabled' => true,
                        'numberType' => CustomFieldTypes::TEXT,
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando customer number',
                            'en-GB' => 'Zalando customer number',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_RETURN_TRACKING_NUMBER),
                    'name' => self::CUSTOM_FIELD_RETURN_TRACKING_NUMBER,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 2,
                        'componentName' => 'sw-field',
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando return tracking number',
                            'en-GB' => 'Zalando return tracking number',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_SALES_CHANNEL_ID),
                    'name' => self::CUSTOM_FIELD_SALES_CHANNEL_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 3,
                        'componentName' => 'sw-field',
                        'disabled' => true,
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando sales channel',
                            'en-GB' => 'Zalando sales channel',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_ORDER_ID),
                    'name' => self::CUSTOM_FIELD_ORDER_ID,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 4,
                        'componentName' => 'sw-field',
                        'disabled' => true,
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando order Id',
                            'en-GB' => 'Zalando order Id',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_STATUS_SENT),
                    'name' => self::CUSTOM_FIELD_STATUS_SENT,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 5,
                        'componentName' => 'sw-field',
                        'disabled' => true,
                        'type' => CustomFieldTypes::TEXT,
                        'customFieldType' => CustomFieldTypes::TEXT,
                        'label' => [
                            'de-DE' => 'Zalando Status Sent',
                            'en-GB' => 'Zalando Status Sent',
                        ],
                    ]
                ],
            ]
        ];
    }
}