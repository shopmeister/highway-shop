<?php

namespace ShopmasterZalandoConnectorSix\Controllers\Api\Zalando;

use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Auth\ApiZalandoAuthService;
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
class AuthZalandoController extends AbstractController
{
    public function __construct(
        readonly private ApiZalandoAuthService $apiZalandoAuthService
    )
    {
    }

    #[Route('/auth',
        name: 'api.action.zalando.outlines.getAuth',
        methods: ['GET'],
    )]
    public function getAuth(Request $request, Context $context): JsonResponse
    {
        try {
            $apiResponse = $this->apiZalandoAuthService->getAuthToken();
        } catch (\Throwable $e) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse($apiResponse->toArray());
    }

    #[Route('/auth/token',
        name: 'api.action.zalando.outlines.getAuthToken',
        methods: ['GET'],
    )]
    public function getAuthToken(Request $request, Context $context): JsonResponse
    {
        try {
            $token = $this->apiZalandoAuthService->getAccessToken();
        } catch (\Throwable $e) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse($token);
    }
}