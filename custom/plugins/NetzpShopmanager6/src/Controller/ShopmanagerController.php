<?php declare(strict_types=1);

namespace NetzpShopmanager6\Controller;

use NetzpShopmanager6\Components\ShopmanagerHelper;
use NetzpShopmanager6\Components\ShopmanagerStatistics;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ShopmanagerController extends AbstractController
{
    final public const PRICEMODE_GROSS   = 'gross';
    final public const PRICEMODE_NET     = 'net';

    public function __construct(private readonly ShopmanagerHelper $helper,
                                private readonly ShopmanagerStatistics $statistics)
    {
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager', name: 'api.action.netzp.shopmanager.check.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager', name: 'api.action.netzp.shopmanager.check', methods: ['GET'])]
    public function testAction(Request $request, Context $context): JsonApiResponse
    {
        $info = $this->getInfo($context);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/shop/{salesChannelId?}', name: 'api.action.netzp.shopmanager.shop.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/shop/{salesChannelId?}', name: 'api.action.netzp.shopmanager.shop', methods: ['GET'])]
    public function shopAction(Request $request, Context $context, $salesChannelId): JsonApiResponse
    {
        $restrictToSalesChannels = $this->helper->getConfig('salesChannels', [], $salesChannelId);

        $info = $this->getInfo($context, $salesChannelId);
        $info['shops'] = $this->helper->getSalesChannels($context, $salesChannelId, $restrictToSalesChannels);
        $info['success'] = $info['shops'] != null ? 1 : 0;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/statistics/{salesChannelId?}', name: 'api.action.netzp.shopmanager.statistics.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/statistics/{salesChannelId?}', name: 'api.action.netzp.shopmanager.statistics', methods: ['GET'])]
    public function statisticsAction(Request $request, Context $context, $salesChannelId = ""): JsonApiResponse
    {
        $info = $this->getInfo($context, $salesChannelId);
        $info = array_merge(
            $info,
            $this->statistics->getStatistics(
                $context,
                $request->query->get('range', 'day'),
                $salesChannelId)
        );
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/orders/{salesChannelId?}', name: 'api.action.netzp.shopmanager.orders.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/orders/{salesChannelId?}', name: 'api.action.netzp.shopmanager.orders', methods: ['GET'])]
    public function ordersAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $orderNumber = $request->query->get('ordernumber', '');

        $listLimit = $this->helper->getConfig('listlimit', 50, $salesChannelId);
        if($listLimit <= 0)
        {
            $listLimit = 50;
        }

        $info = $this->getInfo($context, $salesChannelId);
        if($orderNumber == '')
        {
            $search = $request->query->get('search', '');
            $filter = $request->query->all()['filter'] ?? [];
            $info['data'] = $this->helper->getOrders($context, $salesChannelId, $listLimit, $search, $filter);
        }
        else {
            $info['data'] = $this->helper->getOrder($context, $salesChannelId, $orderNumber);
        }
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/articles/{salesChannelId?}', name: 'api.action.netzp.shopmanager.articles.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/articles/{salesChannelId?}', name: 'api.action.netzp.shopmanager.articles', methods: ['GET'])]
    public function articlesAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $query = $request->query->get('query', '');
        $query = trim($query, '%');
        $query = trim($query);

        $searchArticleNumber = (int)$request->query->get('exact', '0');

        $info = $this->getInfo($context, $salesChannelId);
        $info['data'] = $this->helper->getArticles($context, $salesChannelId, $query, $searchArticleNumber);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/article/{salesChannelId?}', name: 'api.action.netzp.shopmanager.updatearticle.sw63', methods: ['POST'])]
    #[Route(path: '/api/netzp/shopmanager/article/{salesChannelId?}', name: 'api.action.netzp.shopmanager.updatearticle', methods: ['POST'])]
    public function updateArticleAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $info = $this->getInfo($context, $salesChannelId);
        $body = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $info['data'] = $this->helper->updateArticle($context, $salesChannelId, $body);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/toparticles/{salesChannelId?}', name: 'api.action.netzp.shopmanager.toparticles.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/toparticles/{salesChannelId?}', name: 'api.action.netzp.shopmanager.toparticles', methods: ['GET'])]
    public function topArticlesAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $range = $request->query->get('range', 'day');
        $sortMode = (int)$request->query->get('sortmode', '0');

        $listLimit = $this->helper->getConfig('listlimit', 50, $salesChannelId);
        if($listLimit <= 0)
        {
            $listLimit = 50;
        }

        $info = $this->getInfo($context, $salesChannelId);
        $info['data'] = $this->helper->getTopArticles($context, $salesChannelId, $range, $sortMode, $listLimit);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/topcustomers/{salesChannelId?}', name: 'api.action.netzp.shopmanager.topcustomers.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/topcustomers/{salesChannelId?}', name: 'api.action.netzp.shopmanager.topcustomers', methods: ['GET'])]
    public function topCustomersAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $range = $request->query->get('range', 'day');

        $listLimit = $this->helper->getConfig('listlimit', 50, $salesChannelId);
        if($listLimit <= 0) {
            $listLimit = 50;
        }

        $info = $this->getInfo($context, $salesChannelId);
        $info['data'] = $this->helper->getTopCustomers($context, $salesChannelId, $range, $listLimit);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/topsuppliers/{salesChannelId?}', name: 'api.action.netzp.shopmanager.topsuppliers.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/topsuppliers/{salesChannelId?}', name: 'api.action.netzp.shopmanager.topsuppliers', methods: ['GET'])]
    public function topSuppliersAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $range = $request->query->get('range', 'day');
        $sortMode = (int)$request->query->get('sortmode', '0');

        $listLimit = $this->helper->getConfig('listlimit', 50, $salesChannelId);
        if($listLimit <= 0)
        {
            $listLimit = 50;
        }

        $info = $this->getInfo($context, $salesChannelId);
        $info['data'] = $this->helper->getTopSuppliers($context, $salesChannelId, $range, $sortMode, $listLimit);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/customer/{salesChannelId?}', name: 'api.action.netzp.shopmanager.customer.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/customer/{salesChannelId?}', name: 'api.action.netzp.shopmanager.customer', methods: ['GET'])]
    public function customerAction(Request $request, Context $context, $salesChannelId = ''): JsonApiResponse
    {
        $customerNumber = $request->query->get('customernumber', '');

        $info = $this->getInfo($context, $salesChannelId);
        $info['data'] = $this->helper->getCustomer($context, $salesChannelId, $customerNumber);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/setmaintenance/{salesChannelId}', name: 'api.action.netzp.shopmanager.setmaintenance.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/setmaintenance/{salesChannelId}', name: 'api.action.netzp.shopmanager.setmaintenance', methods: ['GET'])]
    public function setMaintenanceAction(Request $request, Context $context, $salesChannelId): JsonApiResponse
    {
        $status = $request->query->get('status', '0');

        $this->helper->setMaintenance($context, $salesChannelId, $status === '1');
        $info = $this->getInfo($context, $salesChannelId);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/setshopmessage/{salesChannelId}', name: 'api.action.netzp.shopmanager.setshopmessage.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/setshopmessage/{salesChannelId}', name: 'api.action.netzp.shopmanager.setshopmessage', methods: ['GET'])]    public function setShopMessageAction(Request $request, Context $context, $salesChannelId): JsonApiResponse
    {
        $msg = $request->query->get('msg', '');

        $this->helper->setShopMessage($context, $salesChannelId, $msg);
        $info = $this->getInfo($context, $salesChannelId);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/states', name: 'api.action.netzp.shopmanager.states.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/states', name: 'api.action.netzp.shopmanager.states', methods: ['GET'])]
    public function getStatesAction(Request $request, Context $context): JsonApiResponse
    {
        $group = $request->query->get('group', '');

        $info = $this->getInfo($context);
        $info['data'] = $this->helper->getStates($context, $group);
        $info['success'] = 1;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/setorderstate', name: 'api.action.netzp.shopmanager.setorderstate.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/setorderstate', name: 'api.action.netzp.shopmanager.setorderstate', methods: ['GET'])]
    public function setOrderStateAction(Request $request, Context $context): JsonApiResponse
    {
        $orderNumber = $request->query->get('ordernumber', '');
        $state = $request->query->get('state', '');

        $info = $this->getInfo($context);
        $data = $this->helper->setOrderState($context, $orderNumber, $state);
        $success = 1;
        if(array_key_exists('error', $data)) {
            $success = 0;
            $info['error'] = $data['error'];
            unset($data['error']);
        }
        $info['data'] = $data;
        $info['success'] = $success;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/setpaymentstate', name: 'api.action.netzp.shopmanager.setpaymentstate.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/setpaymentstate', name: 'api.action.netzp.shopmanager.setpaymentstate', methods: ['GET'])]
    public function setPaymentStateAction(Request $request, Context $context): JsonApiResponse
    {
        $orderNumber = $request->query->get('ordernumber', '');
        $state = $request->query->get('state', '');

        $info = $this->getInfo($context);
        $data = $this->helper->setPaymentState($context, $orderNumber, $state);
        $success = 1;
        if(array_key_exists('error', $data)) {
            $success = 0;
            $info['error'] = $data['error'];
            unset($data['error']);
        }
        $info['data'] = $data;
        $info['success'] = $success;

        return new JsonApiResponse($info);
    }

    #[Route(path: '/api/v{version}/netzp/shopmanager/setdeliverystate', name: 'api.action.netzp.shopmanager.setdeliverystate.sw63', methods: ['GET'])]
    #[Route(path: '/api/netzp/shopmanager/setdeliverystate', name: 'api.action.netzp.shopmanager.setdeliverystate', methods: ['GET'])]
    public function setDeliveryStateAction(Request $request, Context $context): JsonApiResponse
    {
        $orderNumber = $request->query->get('ordernumber', '');
        $state = $request->query->get('state', '');

        $info = $this->getInfo($context);
        $data = $this->helper->setDeliveryState($context, $orderNumber, $state);
        $success = 1;
        if(array_key_exists('error', $data)) {
            $success = 0;
            $info['error'] = $data['error'];
            unset($data['error']);
        }
        $info['data'] = $data;
        $info['success'] = $success;

        return new JsonApiResponse($info);
    }

    // ----------------------------------------

    private function getInfo(Context $context, $salesChannelId = '')
    {
        $priceMode = $this->helper->getConfig('pricemode', self::PRICEMODE_GROSS, $salesChannelId);
        $demoMode = $this->helper->getConfig('demomode', false, $salesChannelId);
        $message = $this->helper->getConfig('message', '', $salesChannelId);

        $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), null, 512, JSON_THROW_ON_ERROR);

        return [
            'apiversion'    => $this->helper->getApiVersion(),
            'pluginversion' => $composer->version,
            'version'       => $this->container->getParameter('kernel.shopware_version'),
            'revision'      => $this->container->getParameter('kernel.shopware_version_revision'),
            'pricemode'     => $priceMode == self::PRICEMODE_GROSS ? 0 : 1,
            'demomode'      => $demoMode,
            'message'       => $message ?? '',
            'timezone'      => $this->getTimezone(),
        ];
    }

    private function getTimezone()
    {
        return date_default_timezone_get();
    }
}
