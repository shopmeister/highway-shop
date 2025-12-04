<?php declare(strict_types=1);


namespace Swag\AmazonPay\Components\Account\Exception;

use Shopware\Core\Checkout\Customer\CustomerException;
use Symfony\Component\HttpFoundation\Response;

class CustomerNotActiveException extends CustomerException
{
    public function __construct(string $id)
    {
        parent::__construct(
            Response::HTTP_UNAUTHORIZED,
            self::CUSTOMER_NOT_FOUND,
            'Customer account "{{ id }}" is not active.',
            ['id' => $id]
        );
    }
}
