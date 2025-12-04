<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Hydrator\Request\CloseChargePermission;

class CloseChargePermissionHydrator implements CloseChargePermissionHydratorInterface
{
    public function hydrate(string $closureReason, bool $cancelPendingCharges = true): array
    {
        return [
            'closureReason' => $closureReason,
            'cancelPendingCharges' => $cancelPendingCharges,
        ];
    }
}
