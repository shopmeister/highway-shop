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
use GuzzleHttp\Utils as GuzzleUtils;

class CreateLabelsRequest extends Request
{
    public function __construct(array $shipments, bool $mustEncode)
    {
        parent::__construct(
            'POST',
            sprintf('orders?combine=false&mustEncode=%s', $mustEncode ? 'true' : 'false'),
            [
                'Content-Type' => 'application/json',
            ],
            GuzzleUtils::jsonEncode(
                [
                    // This is the standard user group profile which defines the billing numbers this user is allowed to use.
                    'profile' => '',
                    'shipments' => $shipments,
                ],
            ),
        );
    }
}
