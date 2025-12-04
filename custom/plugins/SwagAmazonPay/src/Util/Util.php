<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Util;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelperInterface;

class Util
{
    public const ENTITY_REPOSITORY_INTERFACE = '\Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface';

    public static function round(float $price, int $precision): string
    {
        return \number_format(\round($price, $precision), $precision, '.', '');
    }

    public static function getPrecision(?CurrencyEntity $currency): int
    {
        if ($currency === null) {
            return AmazonPayPaymentMethodHelperInterface::DEFAULT_DECIMAL_PRECISION;
        }

        return $currency->getTotalRounding()->getDecimals();
    }

    public static function extendAdminPermissionsForAmazonPayTransactions(Context $context): void{
        if ($context->getSource() instanceof AdminApiSource) {
            if (!$context->getSource()->isAllowed('swag_amazon_pay_transaction:create') && $context->getSource()->isAllowed('order:update')) {
                $data = $context->getSource()->jsonSerialize();
                if (!empty($data['permissions'])) {
                    $permissions = $data['permissions'];
                    $permissions[] = 'swag_amazon_pay_transaction:create';
                    $permissions[] = 'swag_amazon_pay_transaction:read';
                    $permissions[] = 'swag_amazon_pay_transaction:update';
                    $permissions[] = 'order_transaction:read';
                    $permissions[] = 'order_transaction:update';
                    $context->getSource()->setPermissions($permissions);
                }
            }
        }
    }
}
