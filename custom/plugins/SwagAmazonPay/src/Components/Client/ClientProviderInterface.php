<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client;

use AmazonPayApiSdkExtension\Client\Client;
use Amazon\Pay\API\ClientInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;

interface ClientProviderInterface
{
    public const REGION_EU = 'eu';
    public const REGION_US = 'us';
    public const REGION_JP = 'jp';

    /**
     * Returns an AmazonPay V2 API client.
     */
    public function getLegacyClient(?string $salesChannelId = null, ?string $region = self::REGION_EU, ?AmazonPayConfigStruct $config = null): ClientInterface;
    public function getClient(?string $salesChannelId = null, ?string $region = null, ?AmazonPayConfigStruct $config = null): Client;

    /**
     * Returns a list of headers used for API communications.
     */
    public function getHeaders(): array;
}
