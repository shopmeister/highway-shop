<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Pay\Hydrator;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\Button\Pay\Struct\AmazonPayButtonPayloadStruct;

interface AmazonPayButtonPayloadHydratorInterfaceV2
{
    public const DEFAULT_SIGN_IN_SCOPES = [
        'email',
        'shippingAddress',
    ];

    public function hydratePayload(string $salesChannelId, Context $context, bool $oneClickCheckout = false, ?string $customReviewUrl = null): ?AmazonPayButtonPayloadStruct;
}
