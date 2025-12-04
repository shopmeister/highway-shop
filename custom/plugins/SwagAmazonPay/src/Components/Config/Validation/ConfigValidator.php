<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Config\Validation;

use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;

class ConfigValidator implements ConfigValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $configuration): void
    {
        if (empty($configuration['merchantId'])
            && empty($configuration[\sprintf('%s.%s', ConfigServiceInterface::CONFIG_DOMAIN, 'merchantId')])) {
            throw new ConfigValidationException('merchantId');
        }

        if (empty($configuration['publicKeyId'])
            && empty($configuration[\sprintf('%s.%s', ConfigServiceInterface::CONFIG_DOMAIN, 'publicKeyId')])) {
            throw new ConfigValidationException('publicKeyId');
        }

        if (empty($configuration['clientId'])
            && empty($configuration[\sprintf('%s.%s', ConfigServiceInterface::CONFIG_DOMAIN, 'clientId')])) {
            throw new ConfigValidationException('clientId');
        }
    }
}
