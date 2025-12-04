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
use GuzzleHttp\Psr7\Utils as Psr7Utils;

class OAuthTokenRequest extends Request
{
    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $username,
        string $password,
    ) {
        parent::__construct(
            'POST',
            'token',
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            Psr7Utils::streamFor(http_build_query([
                'grant_type' => 'password',
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
                'username' => $username,
                'password' => $password,
            ])),
        );
    }
}
