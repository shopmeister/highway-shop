<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace ShopmasterZalandoConnectorSix\Services\HttpClient\Curl;


use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Event\Curl\CurlExceptionEvent;
use ShopmasterZalandoConnectorSix\Event\HttpClient\HttpClientRequestEvent;
use ShopmasterZalandoConnectorSix\Event\HttpClient\HttpClientResponseEvent;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientInterface;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;


class Client implements ClientInterface
{

    use HandlerTrait;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger->withName('Curl');
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws MethodNameExceptions
     */
    public function request(RequestStruct $request): ResponseStruct
    {
        $this->eventDispatcher->dispatch(new HttpClientRequestEvent($request));
        if (!$request->getMethodName()) {
            throw new MethodNameExceptions('Method name is mandatory');
        }
        $response = $this->{$request->getMethodName()}($request);
        $this->eventDispatcher->dispatch(new HttpClientResponseEvent($response));
        return $response;
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function get(RequestStruct $request): ResponseStruct
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getLink());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeadersByRequest($request));

        return $this->handlerResponse($ch, $request);

    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function post(RequestStruct $request): ResponseStruct
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getLink());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeadersByRequest($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //use for oAuth
        if ($request->getCurlOptUserPwd()) {
            curl_setopt($ch, CURLOPT_USERPWD, $request->getCurlOptUserPwd());
        }

        return $this->handlerResponse($ch, $request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function put(RequestStruct $request): ResponseStruct
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getLink());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeadersByRequest($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $this->handlerResponse($ch, $request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function patch(RequestStruct $request): ResponseStruct
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getLink());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeadersByRequest($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $this->handlerResponse($ch, $request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function delete(RequestStruct $request): ResponseStruct
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getLink());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeadersByRequest($request));

        return $this->handlerResponse($ch, $request);
    }

    /**
     * @throws ClientException
     * @throws ResponseException
     */
    private function handlerResponse($ch, RequestStruct $request): ResponseStruct
    {
        try {
            return $this->getResponseByCurl($ch);
        } catch (ResponseException $exception) {
            $event = (new CurlExceptionEvent())
                ->setException($exception)
                ->setRequest($request);
            $this->eventDispatcher->dispatch($event);
            if ($event->getResponse()) {
                return $event->getResponse();
            } else {
                throw $event->getException();
            }
        }
    }
}