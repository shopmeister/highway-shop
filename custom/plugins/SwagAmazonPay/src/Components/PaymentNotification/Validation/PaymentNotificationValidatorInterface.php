<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PaymentNotification\Validation;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\PaymentNotification\Exception\InvalidPaymentNotificationException;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;
use Symfony\Component\HttpFoundation\Request;

interface PaymentNotificationValidatorInterface
{
    /**
     * The notification version supported by this plugin.
     */
    public const SUPPORTED_VERSION = 'V2';

    /**
     * Validates the provided IPN request.
     *
     * @throws InvalidPaymentNotificationException
     */
    public function validate(Request $request, Context $context): PaymentNotificationMessage;
}
