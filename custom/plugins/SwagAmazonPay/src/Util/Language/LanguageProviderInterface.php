<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Language;

use Shopware\Core\Framework\Context;

interface LanguageProviderInterface
{
    /**
     * Returns a matching language ISO code for the Amazon Pay button.
     */
    public function getAmazonPayButtonLanguage(string $storefrontLanguageId, ?Context $context = null, ?string $salesChannelId = null): string;
}
