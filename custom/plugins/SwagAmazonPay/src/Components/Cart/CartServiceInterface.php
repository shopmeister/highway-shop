<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Cart;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CartServiceInterface
{
    public function isCartIsEmpty(SalesChannelContext $context): bool;
}
