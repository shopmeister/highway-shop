<?php

namespace ShopmasterZalandoConnectorSix\Controllers\Api\Settings;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\LogisticCenter\ApiZalandoLogisticCenterService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RoutePrefix(/api/_action/shopmaster_zalando_connector)
 */
#[Route(
    defaults: ["_routeScope" => ["administration", "api"]]
)]
class GeneralSettingsController extends AbstractController
{
    public function __construct(
        private readonly ApiZalandoSalesChannelsService  $apiZalandoSalesChannelsService,
        private readonly ApiZalandoLogisticCenterService $apiZalandoLogisticCenterService
    )
    {
    }

    #[Route('/settings/general/z_active_sales_channels',
        name: 'api.action.shopmaster_zalando_connector.settings.general.get_z_active_sales_channels',
        methods: ['GET'],
    )]
    public function getZalandoActiveSalesChannels(Context $context): JsonResponse
    {
        try {
            $collection = $this->apiZalandoSalesChannelsService->getCollection();
        } catch (MethodNameExceptions $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse($collection->getActive(), $collection->getResponse()->getStatus());
    }

    /**
     * @throws MethodNameExceptions
     */
    #[Route('/settings/general/z_logistic_centers',
        name: 'api.action.shopmaster_zalando_connector.settings.general.get_z_logistic_centers',
        methods: ['GET'],
    )]
    public function getLogisticCenters(Context $context): JsonResponse
    {
        try {
            $response = $this->apiZalandoLogisticCenterService->getLogisticCenters();
        } catch (\Throwable $exception) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($response->get('items'));
    }
}