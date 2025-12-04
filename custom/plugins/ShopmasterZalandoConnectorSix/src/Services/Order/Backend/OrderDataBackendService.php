<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\Backend;

use ShopmasterZalandoConnectorSix\Exception\Struct\StructException;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\List\OrderListBackendCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\List\OrderListBackendStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\OrderBackendCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend\OrderBackendStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class OrderDataBackendService
{
    private EntityRepository $repositoryOrder;

    public function __construct(
        EntityRepository $repositoryOrder
    )
    {
        $this->repositoryOrder = $repositoryOrder;
    }

    /**
     * Zalando Order Id and Imported Shopware Order Id is same
     *
     * @throws StructException
     */
    public function orderResponseToStruct(ResponseStruct $apiResponse, Context $context): OrderListBackendCollection
    {
        $collection = new OrderListBackendCollection();
        foreach ($apiResponse['data'] as $order) {
            $struct = new OrderListBackendStruct($order['attributes']);
            $struct->setId($order['id']);
            $struct->setType($order["type"]);
            $collection->set(Struct::uuidToId($order['id']), $struct);
        }
        if (!$collection->count()) {
            return $collection;
        }
        $criteria = new Criteria($collection->getKeys());
        $orderCollection = $this->repositoryOrder->search($criteria, $context);
        /** @var OrderEntity $orderEntity */
        foreach ($orderCollection as $orderEntity) {
            /** @var OrderBackendStruct $struct */
            $struct = $collection->get($orderEntity->getId());
            $struct->setOrderEntity($orderEntity);
        }
        return $collection;
    }
}