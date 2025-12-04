<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\Exception\Struct\StructException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\ImportOrderByApiDataMessage;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineSetStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderAddressStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderParamsStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderSetStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

readonly class ApiZalandoOrderService
{
    /**
     * @param ClientService $clientService
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private ClientService       $clientService,
        private MessageBusInterface $messageBus
    )
    {
    }

    /**
     * @param ImportOrderByApiDataMessage $bus
     * @return void
     * @throws ClientException
     * @throws ErrorException
     * @throws MethodNameExceptions
     * @throws ResponseException
     * @throws StructException
     */
    public function dispatchOrderDataForProcessing(ImportOrderByApiDataMessage $bus): void
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_GET)
            ->setUrl('/orders')
            ->addQuery('exported', 'false')
            ->addQuery('page[size]', '5');
        $this->chunkDispatchOrder($request, $bus);

    }

    /**
     * @param RequestStruct $request
     * @param ImportOrderByApiDataMessage $bus
     * @return void
     * @throws ClientException
     * @throws ErrorException
     * @throws MethodNameExceptions
     * @throws ResponseException
     * @throws StructException
     */
    private function chunkDispatchOrder(RequestStruct $request, ImportOrderByApiDataMessage $bus): void
    {
        usleep(100000); //1 sec.
        $response = $this->clientService->request($request);
        $response->isSuccessStatus(true);
        $collection = $this->responseToCollection($response);
        if ($collection->count()) {
            $this->messageBus->dispatch(
                (new Envelope($bus->setOrderCollection($collection)))->with(new DelayStamp(3000))
            );
        }
        if (!empty($response["links"]["next"])) {
            $request->setLink($response["links"]["next"]);
            $this->chunkDispatchOrder($request, $bus);
        }
    }

    /**
     * @param OrderSetStruct $struct
     * @return ResponseStruct
     * @throws ClientException
     */
    public function saveOrder(OrderSetStruct $struct): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_PATCH)
            ->setContentType('application/vnd.api+json')
            ->setUrl('/orders/' . $struct->getId());
        $request->setContent($struct);
        return $this->clientService->request($request);
    }

    /**
     * @param OrderLineSetStruct $struct
     * @return ResponseStruct
     * @throws MethodNameExceptions|ErrorException
     */
    public function saveOrderLine(OrderLineSetStruct $struct): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_PATCH)
            ->setContentType('application/vnd.api+json')
            ->setUrl("/orders/{$struct->getOrderId()}/items/{$struct->getOrderItemId()}/lines/{$struct->getId()}");
        $request->setContent($struct);
        $response = $this->clientService->request($request);
        $response->isSuccessStatus(true);
        return $response;
    }

    /**
     * @return OrderCollection
     * @throws ClientException|ResponseException|StructException
     */
    public function getNewOrders(): OrderCollection
    {
        $request = new RequestStruct();
        $request->setUrl('/orders')
            ->addQuery('exported', 'false')
            ->addQuery('page[size]', '100')
            ->setMethodName($request::METHOD_GET);

        $response = $this->clientService->request($request);
        $response->isSuccessStatus(true);

        return $this->responseToCollection($response);
    }


    /**
     * @param ResponseStruct $response
     * @return OrderCollection
     * @throws ClientException
     * @throws ResponseException
     * @throws StructException
     */
    private function responseToCollection(ResponseStruct $response): OrderCollection
    {
        $collection = new OrderCollection();
        foreach ($response['data'] as $order) {
            $struct = $this->orderDataToStruct($order);
            if ($struct) {
                $collection->set($struct->getId(), $struct);
            }

        }
        return $collection;
    }

    /**
     * @param array $order
     * @return OrderStruct
     * @throws ClientException
     * @throws ResponseException
     * @throws StructException
     */
    private function orderDataToStruct(array $order): ?OrderStruct
    {
        $struct = new OrderStruct($order['attributes']);
        $struct->setId($order['id']);
        $struct->setType($order["type"]);
        if (!$struct->isValideForImport()) {
            return null;
        }
        $struct->setBillingAddress(new OrderAddressStruct($order['attributes']['billing_address']));
        $struct->setShippingAddress(new OrderAddressStruct($order['attributes']['shipping_address']));
        $orderItems = $this->getOrderItems($order["relationships"]["order_items"]["links"]["related"]);
        $struct->setOrderItems($orderItems);
        return $struct;
    }

    /**
     * @param string $link
     * @return OrderItemCollection
     * @throws ClientException
     * @throws ResponseException
     * @throws StructException
     */
    private function getOrderItems(string $link): OrderItemCollection
    {
        $collection = new OrderItemCollection();
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_GET)->setLink($link);
        $response = $this->clientService->request($request);
        $response->isSuccessStatus(true);
        foreach ($response['data'] as $item) {
            $struct = new OrderItemStruct($item['attributes']);
            $struct->setId($item['id']);
            $struct->setType($item['type']);
            $orderLines = $this->getOrderLines($item["relationships"]["order_lines"]["links"]["related"]);
            $struct->setOrderLines($orderLines);
            $collection->add($struct);
        }
        return $collection;
    }

    /**
     * @param string $link
     * @return OrderLineCollection
     * @throws ClientException
     * @throws ResponseException
     * @throws StructException
     */
    private function getOrderLines(string $link): OrderLineCollection
    {
        $collection = new OrderLineCollection();
        $request = new RequestStruct();
        $request->setLink($link)->setMethodName($request::METHOD_GET);
        $response = $this->clientService->request($request);
        $response->isSuccessStatus(true);
        foreach ($response['data'] as $item) {
            $struct = new OrderLineStruct($item['attributes']);
            $struct->setId($item['id']);
            $struct->setType($item['type']);
            $struct->setPrice($item["attributes"]["price"]["amount"]);
            $struct->setDiscountedPrice($item["attributes"]["discounted_price"]["amount"]);
            $struct->setCurrency($item["attributes"]["price"]["currency"]);
            $collection->add($struct);
        }
        return $collection;
    }

    /**
     * @param OrderParamsStruct $params
     * @return ResponseStruct
     */
    public function getOrdersForBackend(OrderParamsStruct $params): ResponseStruct
    {
        return $this->getOrdersByParams($params);
    }

    /**
     * @param OrderParamsStruct $params
     * @return ResponseStruct
     * @throws MethodNameExceptions
     */
    public function getOrdersByParams(OrderParamsStruct $params): ResponseStruct
    {
        $paramsArray = $params->toArray();
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_GET)
            ->setUrl('/orders')
            ->setQuery($paramsArray);
//            ->addQuery('exported', 'false')
//            ->addQuery('page[size]', '5');
        return $this->clientService->request($request);
    }
}