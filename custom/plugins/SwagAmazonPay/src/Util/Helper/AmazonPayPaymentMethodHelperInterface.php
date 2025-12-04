<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Helper;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface AmazonPayPaymentMethodHelperInterface
{
    public const DEFAULT_DECIMAL_PRECISION = 2;

    public function isAmazonPayActive(SalesChannelContext $context): bool;
}
