<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Config\Validation\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class ConfigValidationException extends ShopwareHttpException
{
    /**
     * The name of the field of which the validation failed.
     */
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;

        parent::__construct(
            \sprintf('Invalid value for configuration field [%s]', $field)
        );
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): string
    {
        return 'PLUGIN_SWAG_AMAZON_PAY_CONFIG_VALIDATION_EXCEPTION';
    }
}
