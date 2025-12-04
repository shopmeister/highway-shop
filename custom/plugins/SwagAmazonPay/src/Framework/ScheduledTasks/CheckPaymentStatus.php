<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Framework\ScheduledTasks;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CheckPaymentStatus extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag_amazon_pay.check_payment_status';
    }

    public static function getDefaultInterval(): int
    {
        return 3600;
    }
}
