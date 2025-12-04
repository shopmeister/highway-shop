<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Pay\AddressRestriction;

use Shopware\Core\Framework\Context;

interface AddressRestrictionServiceInterface
{
    /**
     * @return array<string, array>
     */
    public function getAddressRestrictions(string $salesChannelId, Context $context): array;
}
