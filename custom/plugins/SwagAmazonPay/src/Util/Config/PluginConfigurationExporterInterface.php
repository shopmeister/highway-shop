<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Config;

use Shopware\Core\Framework\Context;

interface PluginConfigurationExporterInterface
{
    /**
     * Exports the plugin configuration for any Sales-Channel to an array.
     */
    public function export(Context $context): array;
}
