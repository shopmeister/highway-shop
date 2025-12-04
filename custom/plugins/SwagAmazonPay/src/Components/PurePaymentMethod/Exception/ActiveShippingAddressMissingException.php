<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\PurePaymentMethod\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class ActiveShippingAddressMissingException extends ShopwareHttpException
{
    public function __construct(string $customerId)
    {
        parent::__construct(
            'An active shipping address could not be found for the customer with the id {{ customerId }}.',
            ['customerId' => $customerId]
        );
    }

    public function getErrorCode(): string
    {
        return 'AMAZON_PAY_CHECKOUT__NO_ACTIVE_SHIPPING_ADDRESS_FOR_CUSTOMER';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
