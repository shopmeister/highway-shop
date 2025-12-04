<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession;

interface CreateCheckoutSessionHydratorInterface
{
    public function hydrate(bool $oneClickCheckout = false, string $storeId = '', ?string $salesChannelId = null): array;
}
