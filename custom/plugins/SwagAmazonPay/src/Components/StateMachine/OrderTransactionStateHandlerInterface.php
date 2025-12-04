<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\StateMachine;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;

interface OrderTransactionStateHandlerInterface
{
    public const CONFIG_PAYMENT_STATE_MAPPING_GETTER_PREFIX = 'getPaymentStateMapping';

    public const STATE_TRANSACTION_CHARGE = 'Charge';
    public const STATE_TRANSACTION_PARTIAL_CHARGE = 'PartialCharge';
    public const STATE_TRANSACTION_REFUND = 'Refund';
    public const STATE_TRANSACTION_PARTIAL_REFUND = 'PartialRefund';
    public const STATE_TRANSACTION_CANCEL = 'Cancel';
    public const STATE_TRANSACTION_AUTHORIZE = 'Authorize';

    public function authorize(OrderTransactionEntity $transaction, Context $context): void;

    public function pay(OrderTransactionEntity $transaction, Context $context): void;

    public function payPartially(OrderTransactionEntity $transaction, Context $context): void;

    public function refund(OrderTransactionEntity $transaction, Context $context): void;

    public function refundPartially(OrderTransactionEntity $transaction, Context $context): void;

    public function cancel(OrderTransactionEntity $transaction, Context $context): void;
}
