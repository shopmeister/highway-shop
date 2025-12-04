<?php declare(strict_types=1);

namespace Swag\AmazonPay\Core\AmazonPay\SalesChannel;

use Shopware\Core\System\SalesChannel\SalesChannel\SalesChannelContextSwitcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAmazonPayRoute
{
    abstract public function getDecorated(): AbstractAmazonPayRoute;

    abstract public function checkoutButton(Request $request, SalesChannelContext $context): AmazonPayRouteResponse;

    abstract public function purePayment(Request $request, SalesChannelContext $context): AmazonPayRouteResponse;

    abstract public function purePaymentFinalize(Request $request, SalesChannelContext $context): AmazonPayRouteResponse;

    abstract public function checkoutReview(Request $request, SalesChannelContextSwitcher $contextSwitcher, SalesChannelContext $context): AmazonPayRouteResponse;
}
