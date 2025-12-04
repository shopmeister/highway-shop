<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Login\Hydrator;

use Shopware\Core\Framework\Context;
use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoadedEvent;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\AmazonPay\Components\Button\Login\Struct\AmazonLoginButtonPayloadStruct;

interface AmazonLoginButtonPayloadHydratorInterfaceV3
{
    public const DEFAULT_SIGN_IN_SCOPES = [
        'email',
        'shippingAddress',
        'phoneNumber'
    ];

    /**
     * @param AccountLoginPageLoadedEvent|AccountProfilePageLoadedEvent $event
     */
    public function hydrate(string $salesChannelId, Context $context, PageLoadedEvent $event, array $signInScopes = self::DEFAULT_SIGN_IN_SCOPES): ?AmazonLoginButtonPayloadStruct;
}
