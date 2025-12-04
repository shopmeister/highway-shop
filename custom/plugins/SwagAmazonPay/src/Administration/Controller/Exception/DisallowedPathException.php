<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Administration\Controller\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class DisallowedPathException extends ShopwareHttpException
{
    public function __construct(string $path)
    {
        parent::__construct(
            \sprintf('Disallowed path: %s', $path)
        );
    }

    public function getErrorCode(): string
    {
        return 'PLUGIN_SWAG_AMAZON_PAY_DISALLOWED_PATH';
    }
}
