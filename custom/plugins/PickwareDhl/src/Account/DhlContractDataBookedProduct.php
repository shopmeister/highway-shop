<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Account;

use JsonSerializable;
use Pickware\PickwareDhl\Api\DhlProduct;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class DhlContractDataBookedProduct implements JsonSerializable
{
    private DhlProduct $product;

    /**
     * @var string[]
     */
    private array $billingNumbers;

    public function __construct(DhlProduct $product, array $billingNumbers)
    {
        $this->product = $product;
        $this->billingNumbers = $billingNumbers;
    }

    public function getProduct(): DhlProduct
    {
        return $this->product;
    }

    public function getBillingNumbers(): array
    {
        return $this->billingNumbers;
    }

    public function jsonSerialize(): array
    {
        return [
            'product' => $this->product,
            'billingNumbers' => $this->getBillingNumbers(),
        ];
    }

    public static function createFromMyAccountApi(stdClass $response)
    {
        $allProducts = DhlProduct::getList();
        $bookedProducts = array_map(fn(DhlProduct $product) => new self($product, []), $allProducts);
        $bookedProducts = array_combine(array_map(fn(self $bookedProduct) => $bookedProduct->getProduct()->getCode(), $bookedProducts), $bookedProducts);

        if (!property_exists($response, 'shippingRights')) {
            return array_values(array_filter($bookedProducts, fn(self $bookedProduct) => count($bookedProduct->billingNumbers) !== 0));
        }

        foreach ($response->shippingRights->details as $detail) {
            if (!isset($bookedProducts[$detail->product->key])) {
                continue;
            }

            $bookedProducts[$detail->product->key]->billingNumbers[] = $detail->billingNumber;
        }

        return array_values(array_filter($bookedProducts, fn(self $bookedProduct) => count($bookedProduct->billingNumbers) !== 0));
    }
}
