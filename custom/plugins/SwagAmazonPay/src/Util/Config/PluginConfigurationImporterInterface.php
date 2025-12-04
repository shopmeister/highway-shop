<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Config;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Administration\Controller\Exception\PluginConfigurationImportVersionMismatchException;

interface PluginConfigurationImporterInterface
{
    /**
     * Imports the provided SwagAmazonPay configuration array into the database.
     *
     * @throws PluginConfigurationImportVersionMismatchException
     */
    public function import(array $config, Context $context, bool $ignoreVersions = false): void;
}
