<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\PreferredDelivery;

use Pickware\InstallationLibrary\CustomFieldSet\CustomField;
use Pickware\InstallationLibrary\CustomFieldSet\CustomFieldSet;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class PreferredDeliveryCustomFieldSet extends CustomFieldSet
{
    public const TECHNICAL_NAME = 'pickware_dhl_preferred_delivery_set';

    public function __construct()
    {
        parent::__construct(
            self::TECHNICAL_NAME,
            [
                'label' => [
                    'en-GB' => 'DHL Preferred delivery',
                    'de-DE' => 'DHL Wunschpaket',
                ],
            ],
            [OrderDefinition::ENTITY_NAME],
            [
                new CustomField(
                    'pickware_dhl_preferred_day',
                    CustomFieldTypes::DATETIME,
                    [
                        'label' => [
                            'en-GB' => 'Preferred day',
                            'de-DE' => 'Wunschtag',
                        ],
                    ],
                ),
                new CustomField(
                    'pickware_dhl_preferred_location',
                    CustomFieldTypes::TEXT,
                    [
                        'label' => [
                            'en-GB' => 'Preferred location',
                            'de-DE' => 'Wunschort',
                        ],
                    ],
                ),
                new CustomField(
                    'pickware_dhl_preferred_neighbour',
                    CustomFieldTypes::TEXT,
                    [
                        'label' => [
                            'en-GB' => 'Preferred neighbour',
                            'de-DE' => 'Wunschnachbar',
                        ],
                    ],
                ),
                new CustomField(
                    'pickware_dhl_no_neighbour_delivery',
                    CustomFieldTypes::BOOL,
                    [
                        'type' => 'checkbox',
                        'label' => [
                            'en-GB' => 'No neighbour delivery',
                            'de-DE' => 'Keine Nachbarschaftszustellung',
                        ],
                    ],
                ),
            ],
        );
    }
}
