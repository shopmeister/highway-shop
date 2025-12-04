<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Administration\Controller\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class PluginConfigurationImportVersionMismatchException extends ShopwareHttpException
{
    private array $versions;

    public function __construct(array $versions)
    {
        $this->versions = $versions;
        parent::__construct('Version mismatch while importing config file');
    }

    public function getVersions(): array
    {
        return $this->versions;
    }

    public function getErrorCode(): string
    {
        return 'PLUGIN_SWAG_AMAZON_PAY_IMPORT_PLUGIN_CONFIG_VERSION_MISMATCH';
    }
}
