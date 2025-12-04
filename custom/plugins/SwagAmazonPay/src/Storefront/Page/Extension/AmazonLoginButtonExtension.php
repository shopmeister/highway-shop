<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Storefront\Page\Extension;

class AmazonLoginButtonExtension extends AbstractAmazonButtonExtension
{
    public const PRODUCT_TYPE_LOGIN = 'SignIn';

    public const VALID_PRODUCT_TYPES = [
        self::PRODUCT_TYPE_LOGIN,
    ];

    protected string $productType = self::PRODUCT_TYPE_LOGIN;
}
