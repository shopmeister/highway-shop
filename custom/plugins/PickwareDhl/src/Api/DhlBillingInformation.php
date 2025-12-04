<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlBillingInformation
{
    /**
     * Associative array with: string:productCode => string:billingNumber
     *
     * @var string[]
     */
    private array $billingNumber = [];

    /**
     * The billing number is also known as account number. It is always put together like this:
     *
     * customer number (EKP) + procedure (depends on product) + participation (depends on contract)
     */
    public function getBillingNumberForProduct(DhlProduct $product): string
    {
        if (!isset($this->billingNumber[$product->getCode()])) {
            throw DhlApiClientException::noBillingNumberConfiguredForProduct($product);
        }

        return $this->billingNumber[$product->getCode()];
    }

    public function setBillingNumberForProduct(DhlProduct $dhlProduct, string $billingNumber): void
    {
        $this->billingNumber[$dhlProduct->getCode()] = $billingNumber;
    }
}
