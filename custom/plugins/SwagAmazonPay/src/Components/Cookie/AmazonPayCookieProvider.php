<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class AmazonPayCookieProvider implements CookieProviderInterface
{
    private const requiredCookies = [
        [
            'snippet_name' => 'SwagAmazonPay.cookie.value.name',
            'cookie' => 'swag-amazon-pay',
            'value' => 'activated',
        ],
    ];

    private CookieProviderInterface $originalService;

    public function __construct(CookieProviderInterface $service)
    {
        $this->originalService = $service;
    }

    public function getCookieGroups(): array
    {
        $groups = $this->originalService->getCookieGroups();
        $index = \array_search('cookie.groupRequired', \array_column($groups, 'snippet_name'), true);

        if ($index !== false) {
            $groups[$index]['entries'] = \array_merge($groups[$index]['entries'], self::requiredCookies);
        }

        return $groups;
    }
}
