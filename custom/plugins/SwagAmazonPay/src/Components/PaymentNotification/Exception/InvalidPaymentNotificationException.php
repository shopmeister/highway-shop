<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PaymentNotification\Exception;

class InvalidPaymentNotificationException extends \Exception
{
    private string $notificationBody;

    public function __construct(
        string $message = 'The payment notification request is invalid.',
        string $notificationBody = ''
    ) {
        $this->notificationBody = $notificationBody;

        parent::__construct(
            $message
        );
    }

    public function getNotificationBody(): string
    {
        return $this->notificationBody;
    }
}
