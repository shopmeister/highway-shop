<?php

namespace ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\CustomFieldsInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class ProductCustomFields implements CustomFieldsInterface
{

    const CUSTOM_FIELD_INDIVIDUAL_STOCK = 'sm_individualStock';
    const CUSTOM_FIELD_INDIVIDUAL_COVER = 'sm_cover';
    const CUSTOM_FIELD_INDIVIDUAL_PICTURE_2 = 'sm_pic_2';
    const CUSTOM_FIELD_INDIVIDUAL_PICTURE_3 = 'sm_pic_3';
    const CUSTOM_FIELD_INDIVIDUAL_PICTURE_4 = 'sm_pic_4';
    const CUSTOM_FIELD_INDIVIDUAL_PICTURE_5 = 'sm_pic_5';
    const CUSTOM_FIELD_INDIVIDUAL_PICTURE_6 = 'sm_pic_6';
    const CUSTOM_FIELD_EXCLUDE_FROM_ZALANDO = 'sm_excludeFromZalando';
    const PRODUCT_ADDITIONAL_SET_NAME = 'sm_products_additional';

    public static function getSetId(): string
    {
        return '3f007b7a4204e9e799929ce3c988c692';
    }

    public static function getSet(): array
    {
        return [
            'id' => self::getSetId(),
            'name' => self::PRODUCT_ADDITIONAL_SET_NAME,
            'active' => true,
            'config' => [
                'label' => [
                    'de-DE' => 'Zalando Products',
                    'en-GB' => 'Zalando Products'
                ]
            ],
            'relations' => [
                ['entityName' => ProductDefinition::ENTITY_NAME]
            ],
            'customFields' => [
                [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_STOCK),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_STOCK,
                    'type' => CustomFieldTypes::INT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 0,
                        'componentName' => 'sw-field',
                        'numberType' => CustomFieldTypes::INT,
                        'type' => 'number',
                        'customFieldType' => 'number',
                        'label' => [
                            'de-DE' => 'Individueller Zalando-Bestand',
                            'en-GB' => 'individual Zalando stock',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_COVER),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_COVER,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 1,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Vorschaubild',
                            'en-GB' => 'Zalando cover',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_2),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_2,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 2,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Bild 2',
                            'en-GB' => 'Zalando picture 2',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_3),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_3,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 3,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Bild 3',
                            'en-GB' => 'Zalando picture 3',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_4),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_4,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 4,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Bild 4',
                            'en-GB' => 'Zalando picture 4',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_5),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_5,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 5,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Bild 5',
                            'en-GB' => 'Zalando picture 5',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_6),
                    'name' => self::CUSTOM_FIELD_INDIVIDUAL_PICTURE_6,
                    'type' => CustomFieldTypes::TEXT,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 6,
                        'componentName' => 'sw-media-field',
                        'customFieldType' => CustomFieldTypes::MEDIA,
                        'label' => [
                            'de-DE' => 'Zalando Bild 6',
                            'en-GB' => 'Zalando picture 6',
                        ],
                    ]
                ], [
                    'id' => md5(self::CUSTOM_FIELD_EXCLUDE_FROM_ZALANDO),
                    'name' => self::CUSTOM_FIELD_EXCLUDE_FROM_ZALANDO,
                    'type' => CustomFieldTypes::BOOL,
                    'active' => true,
                    'config' => [
                        'customFieldPosition' => 7,
                        'componentName' => 'sw-switch-field',
                        'customFieldType' => 'checkbox',
                        'label' => [
                            'de-DE' => 'Von Zalando ausschließen',
                            'en-GB' => 'Exclude from Zalando',
                        ],
                    ]
                ],
            ]
        ];
    }
}