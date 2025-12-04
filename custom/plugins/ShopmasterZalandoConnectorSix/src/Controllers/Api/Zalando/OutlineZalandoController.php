<?php

namespace ShopmasterZalandoConnectorSix\Controllers\Api\Zalando;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Outline\ApiZalandoOutlineService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RoutePrefix(/api/_action/shopmaster_zalando_connector)
 */
#[Route(
    defaults: ["_routeScope" => ["administration", "api"]]
)]
class OutlineZalandoController extends AbstractController
{
    public function __construct(
        readonly private ApiZalandoOutlineService $apiZalandoOutlineService
    )
    {
    }

    #[Route('/outline',
        name: 'api.action.zalando.outlines.getOutlines',
        methods: ['GET'],
    )]
    public function getOutlines(Request $request, Context $context): JsonResponse
    {
        try {
            $apiResponse = $this->apiZalandoOutlineService->getOutlines();
            return new JsonResponse($apiResponse->get('items'));
        } catch (MethodNameExceptions $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
    }
}