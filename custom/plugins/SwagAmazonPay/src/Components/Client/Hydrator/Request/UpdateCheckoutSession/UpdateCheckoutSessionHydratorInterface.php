<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Client\Hydrator\Request\UpdateCheckoutSession;

use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

interface UpdateCheckoutSessionHydratorInterface
{
    public const PAYMENT_INTENT_CONFIRM = 'Confirm';
    public const PAYMENT_INTENT_AUTHORIZE = 'Authorize';

    /**
     * Hydrates an update request for the CheckoutSession. Use the data to notify
     */
    public function hydrate(
        AsyncPaymentTransactionStruct $pendingShopwareTransaction,
        CurrencyEntity $currency,
        Context $context,
        string $paymentIntent = self::PAYMENT_INTENT_AUTHORIZE,
        string $noteToBuyer = ''
    ): array;
}
