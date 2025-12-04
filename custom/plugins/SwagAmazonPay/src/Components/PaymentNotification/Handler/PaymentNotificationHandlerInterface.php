<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PaymentNotification\Handler;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\PaymentNotification\Exception\PaymentNotificationProcessException;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;

interface PaymentNotificationHandlerInterface
{
    /**
     * A value indicating if a notification object is of type 'charge'
     */
    public const NOTIFICATION_OBJECT_TYPE_CHARGE = 'CHARGE';

    /**
     * A value indicating if a notification object is of type 'refund'
     */
    public const NOTIFICATION_OBJECT_TYPE_REFUND = 'REFUND';

    /**
     * Returns a value indicating if the handler supports the given objectType
     *
     * @param string $objectType Type of object associated with the IPN message
     */
    public function supports(string $objectType): bool;

    /**
     * Processes the provided IPN
     *
     * @throws PaymentNotificationProcessException
     */
    public function process(PaymentNotificationMessage $notificationMessage, Context $context): void;
}
