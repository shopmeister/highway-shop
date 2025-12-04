<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Account\Hydrator;

use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface RegistrationDataHydratorInterface
{
    /**
     * Hydrates a shopware account registration compliant DataBag by using the given Amazon Pay Checkout-Session. The DataBag can be used
     * in the AccountRegistrationService to register a new Shopware account.
     */
    public function hydrateCustomerDataBag(array $checkoutSession, SalesChannelContext $context): DataBag;

    /**
     * Hydrates a shopware account address by the provided Amazon Pay address.
     */
    public function hydrateAddressDataBag(array $amazonPayAddress, SalesChannelContext $salesChannelContext): DataBag;

    /**
     * Hydrates a shopware account form registration compliant DataBag by requesting buyer information via given buyerToken.
     */
    public function hydrateBuyerInformation(string $buyerToken, SalesChannelContext $salesChannelContext): DataBag;
}
