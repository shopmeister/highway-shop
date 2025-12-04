<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util;

use Composer\Autoload\ClassLoader;

final class SwagAmazonPayClassLoader extends ClassLoader
{
    private const VENDOR_DEPENDENCIES_PSR4 = [
        'TheIconic\\NameParser\\' => 'theiconic/name-parser/src/',
        'Amazon\\Pay\\API\\' => 'amzn/amazon-pay-api-sdk-php/Amazon/Pay/API',
        'phpseclib\\' => 'phpseclib/phpseclib/phpseclib',
        'phpseclib3\\' => 'phpseclib/phpseclib/phpseclib',
        'AmazonPayApiSdkExtension\\' => 'mkreusch/amazon-pay-api-sdk-php-extension',
    ];

    public function __construct()
    {
        $this->addPsr4Dependencies();
    }

    /**
     * Iterates over self::VENDOR_DEPENDENCIES_PSR4 to register available namespaces.
     */
    private function addPsr4Dependencies(): void
    {
        $vendorDir = __DIR__ . '/../../vendor/';

        foreach (self::VENDOR_DEPENDENCIES_PSR4 as $prefix => $relativePath) {
            $path = $vendorDir . $relativePath;

            if (!\file_exists($path)) {
                continue;
            }

            $this->addPsr4($prefix, $path);
        }
    }
}
