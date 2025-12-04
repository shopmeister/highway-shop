<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Account;

use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Account\Exception\CustomerNotActiveException;
use Swag\AmazonPay\Components\Account\Struct\AmazonLoginDataStruct;

interface AmazonPayAccountServiceInterfaceV2
{
    /**
     * Registers a new user to shopware using the provided information within the checkout session.
     *
     * @return string the id of the customer which has been created
     */
    public function registerCustomerOrGuest(array $checkoutSession, SalesChannelContext $salesChannelContext, bool $isGuest = true): string;

    /**
     * Updates the customer's current addresses and default payment method to the latest version of the checkout session.
     */
    public function updateCustomer(string $customerId, array $checkoutSession, SalesChannelContext $salesChannelContext): void;

    /**
     * Gets a customer's id by the specified Amazon Pay account id.
     */
    public function getActiveCustomerId(AmazonLoginDataStruct $amazonLoginDataStruct, SalesChannelContext $salesChannelContext): ?string;

    /**
     * Login customer by given Amazon account id, provide customerId specific account.
     *
     * @throws CustomerNotFoundException
     * @throws CustomerNotActiveException
     */
    public function loginByAmazonAccount(AmazonLoginDataStruct $amazonLoginDataStruct, SalesChannelContext $context): string;

    /**
     * Will perform a login by given customer id.
     */
    public function loginByCustomerId(string $customerId, SalesChannelContext $context): string;

    /**
     * Sets the amazon account id custom field for the specified customer.
     */
    public function setAmazonPayAccountId(string $customerId, string $amazonAccountId, Context $context): void;

    /**
     * Sets password and password confirmation for registration data.
     */
    public function setRandomDefaultPassword(DataBag $data): void;

    /**
     * Returns the customer id if an Amazon customer for that email was found or null.
     */
    public function getAmazonCustomerIdByEmail(string $email, SalesChannelContext $salesChannelContext): ?string;

    /**
     * Returns a customer id if the given Amazon account id is linked with one or null.
     */
    public function getCustomerIdByBuyerId(string $amazonAccountId, SalesChannelContext $salesChannelContext): ?string;
}
