<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Validation;

use AmazonPayApiSdkExtension\Struct\CheckoutSession;
use Swag\AmazonPay\Components\Client\Validation\Exception\PaymentDeclinedException;
use Swag\AmazonPay\Components\Client\Validation\Exception\ResponseValidationException;

interface ResponseValidatorInterface
{
    /**
     * @throws ResponseValidationException
     * @throws PaymentDeclinedException
     */
    public function validateResponse(CheckoutSession $checkoutSession): bool;
}
