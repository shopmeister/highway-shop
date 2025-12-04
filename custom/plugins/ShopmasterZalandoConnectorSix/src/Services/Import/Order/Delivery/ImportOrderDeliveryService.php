<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Order\Delivery;

use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Delivery\ImportOrderDeliveryCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Delivery\ImportOrderDeliveryStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\ImportOrderStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ImportOrderDeliveryService
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;
    /**
     * @var EntityRepository
     */
    private EntityRepository $repositoryStateMachineState;
    /**
     * @var EntityRepository
     */
    private EntityRepository $repositoryShippingMethod;

    /**
     * @param ConfigService $configService
     * @param EntityRepository $repositoryStateMachineState
     * @param EntityRepository $repositoryShippingMethod
     */
    public function __construct(
        ConfigService    $configService,
        EntityRepository $repositoryStateMachineState,
        EntityRepository $repositoryShippingMethod
    )
    {
        $this->configService = $configService;
        $this->repositoryStateMachineState = $repositoryStateMachineState;
        $this->repositoryShippingMethod = $repositoryShippingMethod;
    }

    /**
     * @param ImportOrderStruct $importOrderStruct
     * @param Context $context
     * @return ImportOrderDeliveryCollection
     */
    public function getDeliveries(ImportOrderStruct $importOrderStruct, Context $context): ImportOrderDeliveryCollection
    {
        $collection = new ImportOrderDeliveryCollection();
        $collection->add($this->getDelivery($importOrderStruct, $context));
        return $collection;
    }

    /**
     * @param ImportOrderStruct $importOrderStruct
     * @param Context $context
     * @return ImportOrderDeliveryStruct
     * @throws \Exception
     */
    private function getDelivery(ImportOrderStruct $importOrderStruct, Context $context): ImportOrderDeliveryStruct
    {
        $orderConfig = $this->configService->getImportOrderConfigBySalesChannelId($importOrderStruct->getOrderStruct()->getSalesChannelId());
        $struct = new ImportOrderDeliveryStruct();
        $struct->setStateId($orderConfig->getDeliveryStateId() ?? $this->getDefaultStateId($context));
        $struct->setShippingMethodId($orderConfig->getShippingMethodId() ?? $this->getDefaultShippingMethodId($context));
        $struct->setShippingCosts($importOrderStruct->getShippingCosts());
        $struct->setShippingDateEarliest(new \DateTime($importOrderStruct->getOrderStruct()->getCreatedAt()));
        $struct->setShippingDateLatest(new \DateTime($importOrderStruct->getOrderStruct()->getDeliveryEndDate()));
        return $struct;
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getDefaultStateId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addAssociation('stateMachine');
        $criteria->addFilter(new EqualsAnyFilter('stateMachine.technicalName', [OrderDeliveryStates::STATE_MACHINE]))
            ->addFilter(new EqualsFilter('technicalName', OrderDeliveryStates::STATE_OPEN));
        return $this->repositoryStateMachineState->searchIds($criteria, $context)->firstId();
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getDefaultShippingMethodId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        return $this->repositoryShippingMethod->searchIds($criteria, $context)->firstId();
    }
}