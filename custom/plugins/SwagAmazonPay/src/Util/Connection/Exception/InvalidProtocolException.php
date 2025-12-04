<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Connection\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class InvalidProtocolException extends ShopwareHttpException
{
    public function getErrorCode(): string
    {
        return 'PLUGIN_SWAG_AMAZON_PAY_INVALID_PROTOCOL';
    }
}
