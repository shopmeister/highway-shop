<?php declare(strict_types=1);

namespace NetzpShopmanager6\Controller;

use NetzpShopmanager6\Components\ShopmanagerVisitors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class StatisticsController extends StorefrontController
{
    public function __construct(private readonly ShopmanagerVisitors $visitors)
    {
    }

    #[Route(path: '/netzp/shopmanager/statistics', name: 'netzp.shopmanager.statistics', defaults: ['XmlHttpRequest' => true], options: ['seo' => false], methods: ['GET'])]
    public function countStatistics(SalesChannelContext $context, Request $request)
    {
        $this->visitors->logStats($context->getContext(), $context->getSalesChannel()->getId(), $request);

        return new Response();
    }
}
