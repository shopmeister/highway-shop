<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Framework\ScheduledTasks;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class SignUpTokenCleanUp extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag_amazon_pay.sign_up_token_clean_up';
    }

    public static function getDefaultInterval(): int
    {
        // 1w(604800) = 60s * 60(min) * 24(h) * 7(d)
        return 604800;
    }
}
