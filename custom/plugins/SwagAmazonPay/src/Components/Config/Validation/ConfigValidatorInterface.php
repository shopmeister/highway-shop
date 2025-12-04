<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Config\Validation;

use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;

interface ConfigValidatorInterface
{
    /**
     * Validates the provided configuration.
     *
     * @throws ConfigValidationException
     */
    public function validate(array $configuration): void;
}
