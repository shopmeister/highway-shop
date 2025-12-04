<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Administration\Controller\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class PluginConfigurationImportException extends ShopwareHttpException
{
    public function __construct(\Exception $previousException)
    {
        parent::__construct(
            'Importing the plugin configuration failed due to the following exception: {{ exceptionMessage }}',
            ['exceptionMessage' => $previousException->getMessage()],
            $previousException
        );
    }

    public function getErrorCode(): string
    {
        return 'PLUGIN_SWAG_AMAZON_PAY_IMPORT_PLUGIN_CONFIG';
    }
}
