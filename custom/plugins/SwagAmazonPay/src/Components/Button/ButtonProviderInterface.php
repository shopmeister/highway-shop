<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button;

use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonLoginButtonExtension;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonPayButtonExtension;

interface ButtonProviderInterface
{
    public function getAmazonPayButton(PageLoadedEvent $event): ?AmazonPayButtonExtension;

    public function getAmazonLoginButton(PageLoadedEvent $event): ?AmazonLoginButtonExtension;
}
