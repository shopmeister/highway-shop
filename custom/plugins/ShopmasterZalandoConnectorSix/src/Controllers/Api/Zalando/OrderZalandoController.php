<?php

namespace ShopmasterZalandoConnectorSix\Controllers\Api\Zalando;

use ShopmasterZalandoConnectorSix\Exception\Struct\StructException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order\ApiZalandoOrderService;
use ShopmasterZalandoConnectorSix\Services\Order\Backend\OrderDataBackendService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderParamsStruct;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;


/**
 * RoutePrefix(/api/_action/shopmaster_zalando_connector)
 */
#[Route(
    defaults: ["_routeScope" => ["api"]]
)]
class OrderZalandoController extends AbstractController
{

    public function __construct(
        private readonly ApiZalandoOrderService  $apiZalandoOrderService,
        private readonly OrderDataBackendService $orderDataBackendService,
    )
    {
    }

    #[Route('/order/list/backend',
        name: 'api.action.zalando.order.getOrderForBackend',
        methods: ['POST'],
    )]
    public function getOrderForBackend(Request $request, Context $context): JsonResponse
    {
        $orderParamsStruct = new OrderParamsStruct();
        $orderParamsStruct->setPageSize(25);
        $apiResponse = $this->apiZalandoOrderService->getOrdersForBackend($orderParamsStruct);
        try {
            $collection = $this->orderDataBackendService->orderResponseToStruct($apiResponse, $context);
            return new JsonResponse($collection);
        } catch (StructException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

    }
}