<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\ApiErrorHandlingBundle\ServerOverloadException;

use Exception;
use Pickware\HttpUtils\JsonApi\JsonApiErrors;
use Pickware\HttpUtils\JsonApi\JsonApiErrorsSerializable;
use Throwable;

/**
 * This exception can be used to indicate that this error is caused by a server overload and inform the client that
 * a retry some moments later will fix the issue
 */
class ServerOverloadException extends Exception implements JsonApiErrorsSerializable
{
    public function __construct(
        private readonly JsonApiErrors $jsonApiErrors,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $this->jsonApiErrors->getThrowableMessage(),
            previous: $previous,
        );
    }

    public function serializeToJsonApiErrors(): JsonApiErrors
    {
        return $this->jsonApiErrors;
    }
}
