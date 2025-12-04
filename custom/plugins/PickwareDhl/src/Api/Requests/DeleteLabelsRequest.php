<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Requests;

use GuzzleHttp\Psr7\Request;

class DeleteLabelsRequest extends Request
{
    public function __construct(array $shipmentNumbers)
    {
        // This is the standard user group profile which defines the billing numbers this user is allowed to use.
        $httpQuery = http_build_query([
            'profile' => '',
        ]);
        foreach ($shipmentNumbers as $shipmentNumber) {
            $httpQuery = sprintf('%s&%s', $httpQuery, http_build_query(['shipment' => $shipmentNumber]));
        }

        parent::__construct(
            'DELETE',
            sprintf(
                'orders?%s',
                $httpQuery,
            ),
        );
    }
}
