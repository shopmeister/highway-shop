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

class PaymentFinalizeResponseValidator implements ResponseValidatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws ResponseValidationException
     * @throws PaymentDeclinedException
     */
    public function validateResponse(CheckoutSession $checkoutSession): bool
    {
        $statusDetails = $checkoutSession->getStatusDetails() ?? null;

        if (!$statusDetails) {
            throw new ResponseValidationException('Could not determine status of the pending checkout session.', $checkoutSession->toArray());
        }

        if ($statusDetails->getReasonCode() === 'Declined') {
            throw new PaymentDeclinedException(\sprintf('The Amazon Pay checkout has been declined. Code: [%s], Description: [%s]', $statusDetails->getReasonCode(), $statusDetails->getReasonDescription()), $checkoutSession->toArray());
        }

        if ($statusDetails->getState() === 'Canceled') {
            throw new ResponseValidationException(\sprintf('The Amazon Pay checkout has been canceled. Code: [%s], Description: [%s]', $statusDetails->getReasonCode(), $statusDetails->getReasonDescription()), $checkoutSession->toArray());
        }

        return true;
    }
}
