<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PurePaymentMethod\Hydrator;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\PurePaymentMethod\Struct\AmazonPayPurePaymentMethodPayloadStruct;

interface AmazonPayPurePaymentMethodPayloadHydratorInterface
{
    public const SIGN_IN_SCOPES_DEFAULT = [
        'name',
        'email',
        'phoneNumber',
        'billingAddress',
    ];

    public function hydrate(SalesChannelContext $salesChannelContext, CustomerEntity $customerEntity, OrderTransactionEntity $orderTransaction): ?AmazonPayPurePaymentMethodPayloadStruct;
}
