<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Order\Transaction;

use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\ImportOrderStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Transaction\ImportOrderTransactionCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Transaction\ImportOrderTransactionStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class ImportOrderTransactionService
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
    private EntityRepository $repositoryPaymentMethod;

    /**
     * @param ConfigService $configService
     * @param EntityRepository $repositoryStateMachineState
     * @param EntityRepository $repositoryPaymentMethod
     */
    public function __construct(
        ConfigService             $configService,
        EntityRepository          $repositoryStateMachineState,
        EntityRepository $repositoryPaymentMethod
    )
    {
        $this->configService = $configService;
        $this->repositoryStateMachineState = $repositoryStateMachineState;
        $this->repositoryPaymentMethod = $repositoryPaymentMethod;
    }

    /**
     * @param ImportOrderStruct $importOrderStruct
     * @param Context $context
     * @return ImportOrderTransactionCollection
     */
    public function getTransactions(ImportOrderStruct $importOrderStruct, Context $context): ImportOrderTransactionCollection
    {

        $collection = new ImportOrderTransactionCollection();
        $collection->add($this->getTransactionStruct($importOrderStruct, $context));
        return $collection;
    }

    /**
     * @param ImportOrderStruct $importOrderStruct
     * @param Context $context
     * @return ImportOrderTransactionStruct
     */
    private function getTransactionStruct(ImportOrderStruct $importOrderStruct, Context $context): ImportOrderTransactionStruct
    {
        $orderConfig = $this->configService->getImportOrderConfigBySalesChannelId($importOrderStruct->getOrderStruct()->getSalesChannelId());
        $struct = new ImportOrderTransactionStruct();
        $struct->setStateId($orderConfig->getPaymentStateId() ?? $this->getDefaultStateId($context));
        $struct->setPaymentMethodId($orderConfig->getPaymentMethodId() ?? $this->getDefaultPaymentMethodId($context));
        $struct->setAmount($importOrderStruct->getPaymentPrice());
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
        $criteria->addFilter(new EqualsAnyFilter('stateMachine.technicalName', [OrderTransactionStates::STATE_MACHINE]))
            ->addFilter(new EqualsFilter('technicalName', OrderTransactionStates::STATE_OPEN));
        return $this->repositoryStateMachineState->searchIds($criteria, $context)->firstId();
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getDefaultPaymentMethodId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pluginId', null))
            ->addSorting(new FieldSorting('active', FieldSorting::DESCENDING));
        return $this->repositoryPaymentMethod->searchIds($criteria, $context)->firstId();
    }
}