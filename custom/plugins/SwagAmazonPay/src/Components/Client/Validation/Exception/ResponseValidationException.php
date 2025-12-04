<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Validation\Exception;

class ResponseValidationException extends \Exception
{
    private array $responseData;

    public function __construct(
        string $message = 'Could not validate response.',
        array $responseData = []
    ) {
        $this->responseData = $responseData;

        parent::__construct(
            $message
        );
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
