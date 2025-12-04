<?php

namespace ShopmasterZalandoConnectorSix\Subscribers\Curl;

use Psr\Cache\InvalidArgumentException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\TooManyRequestsException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\UnauthorizedResponseException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Auth\ApiZalandoAuthService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Event\Curl\CurlExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CurlExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ClientService
     */
    private ClientService $clientService;
    /**
     * @var ApiZalandoAuthService
     */
    private ApiZalandoAuthService $apiZalandoAuthService;

    /**
     * @param ClientService $clientService
     * @param ApiZalandoAuthService $apiZalandoAuthService
     */
    public function __construct(
        ClientService         $clientService,
        ApiZalandoAuthService $apiZalandoAuthService
    )
    {
        $this->clientService = $clientService;
        $this->apiZalandoAuthService = $apiZalandoAuthService;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CurlExceptionEvent::class => 'onCurlExceptionEvent'
        ];
    }

    /**
     * @param CurlExceptionEvent $event
     * @return void
     * @throws InvalidArgumentException
     * @throws MethodNameExceptions
     */
    public function onCurlExceptionEvent(CurlExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if ($event->getException() instanceof TooManyRequestsException) {
            sleep($event->getException()->getRetryAfter() + rand(2, 10));
            $response = $this->clientService->request($request); //recursive
            $event->setResponse($response);
        } elseif ($event->getException() instanceof UnauthorizedResponseException) {
            $request->setZOAuthToken(null);
            $this->apiZalandoAuthService->reset();
            sleep(1);
            $response = $this->clientService->request($request); //recursive
            $event->setResponse($response);
        }
    }
}