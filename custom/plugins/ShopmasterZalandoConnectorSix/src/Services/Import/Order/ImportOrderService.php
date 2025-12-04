<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Order;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Event\Order\OrderPlacedCriteriaEvent;
use ShopmasterZalandoConnectorSix\Event\Order\OrderPlacedEvent;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\Order\ExceptionImportOrder;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order\ApiZalandoOrderService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Address\ImportOrderAddressService;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Delivery\ImportOrderDeliveryService;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Item\ImportOrderItemService;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Price\ImportOrderPriceService;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Transaction\ImportOrderTransactionService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderSetStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Address\ImportOrderAddressCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Address\ImportOrderAddressStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Customer\ImportOrderCustomerStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\ImportOrderStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Util\Json;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ImportOrderService
{
    /**
     * @var Logger
     */
    private LoggerInterface $logger;

    /**
     * @param Logger $logger
     * @param ApiZalandoOrderService $apiZalandoOrderService
     * @param ImportOrderPriceService $importOrderPriceService
     * @param StateMachineRegistry $stateMachineRegistry
     * @param ConfigService $configService
     * @param NumberRangeValueGeneratorInterface $numberRangeValueGenerator
     * @param ImportOrderItemService $importOrderItemService
     * @param EntityRepository $repositoryOrder
     * @param ImportOrderAddressService $importOrderAddressService
     * @param ImportOrderTransactionService $importOrderTransactionService
     * @param ImportOrderDeliveryService $importOrderDeliveryService
     * @param EntityRepository $repositorySalesChannel
     * @param EventDispatcherInterface $eventDispatcher
     * @param AbstractSalesChannelContextFactory $salesChannelContextFactory
     */
    public function __construct(
        LoggerInterface                                     $logger,
        private readonly ApiZalandoOrderService             $apiZalandoOrderService,
        private readonly ImportOrderPriceService            $importOrderPriceService,
        private readonly StateMachineRegistry               $stateMachineRegistry,
        private readonly ConfigService                      $configService,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly ImportOrderItemService             $importOrderItemService,
        private readonly EntityRepository                   $repositoryOrder,
        private readonly ImportOrderAddressService          $importOrderAddressService,
        private readonly ImportOrderTransactionService      $importOrderTransactionService,
        private readonly ImportOrderDeliveryService         $importOrderDeliveryService,
        private readonly EntityRepository                   $repositorySalesChannel,
        private readonly EventDispatcherInterface           $eventDispatcher,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory
    )
    {
        $this->logger = $logger->withName('ImportOrderService');
    }

    /**
     * @param OrderCollection $newOrders
     * @param Context|null $mainContext
     * @return void
     */
    public function makeOrdersByCollection(OrderCollection $newOrders, ?Context $mainContext = null): void
    {
        if (!$mainContext) {
            $mainContext = $this->makeContext();
        }

        /** @var OrderStruct $order */
        foreach ($newOrders as $order) {
            if (!$order->isValideForImport()) {
                continue;
            }
            $orderConfig = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
            if (!$orderConfig->isActive()) {
                continue;
            }
            $context = clone $mainContext;
            try {
                $this->addLanguageInContext($order, $context);

                $addressesCollection = $this->makeAddressesCollection($order, $context);
                $customerStruct = $this->makeCustomerStruct($order, $addressesCollection->getBillingAddress(), $context);

                $importOrderStruct = $this->makeImportOrderStruct($order, $context);
                $importOrderStruct->setAddresses($addressesCollection);
                $importOrderStruct->setOrderCustomer($customerStruct);

                $transactions = $this->importOrderTransactionService->getTransactions($importOrderStruct, $context);
                $importOrderStruct->setTransactions($transactions);

                $deliveries = $this->importOrderDeliveryService->getDeliveries($importOrderStruct, $context);
                $importOrderStruct->setDeliveries($deliveries);

                $data = $importOrderStruct->toArray();
                $this->logger->info('Payload for Shopware Order Creation', ['data' => $data]);
                $this->repositoryOrder->create([$data], $context);
                $this->setExportedInZalando($order);
                $this->dispatch($order, $context);
            } catch (\Throwable $exception) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
            }
        }
    }

    /**
     * @param OrderStruct $order
     * @param Context $context
     * @return ImportOrderStruct
     * @throws ExceptionImportOrder|\JsonException
     */
    private function makeImportOrderStruct(OrderStruct $order, Context $context): ImportOrderStruct
    {


        $lineItems = $this->getLineItems($order, $context);
        $orderPrice = $this->importOrderPriceService->calculateOrderCartPrice($order, $context);
        $paymentPrice = $this->importOrderPriceService->calculatePaymentTotal($order, $context);
        $shippingCosts = $this->importOrderPriceService->calculateShippingCosts($order, $context);
        $stateId = $this->getStateId($order, $context);
        $currencyId = $context->getCurrencyId();
        $salesChannelId = $this->getSalesChannelId($order);
        $languageId = $context->getLanguageId();
        $orderNumber = $this->getOrderNumber($order);

        $struct = new ImportOrderStruct();
        $struct->setLineItems($lineItems)
            ->setPrice($orderPrice)
            ->setShippingCosts($shippingCosts)
            ->setStateId($stateId)
            ->setCurrencyId($currencyId)
            ->setSalesChannelId($salesChannelId)
            ->setLanguageId($languageId)
            ->setOrderNumber($orderNumber)
            ->setCurrencyFactor($context->getCurrencyFactor())
            ->setOrderDateTime($order->getOrderDate())
            ->setPaymentPrice($paymentPrice)
            ->setOrderStruct($order)
            ->setItemRounding(json_decode(Json::encode($context->getRounding()), true, 512, \JSON_THROW_ON_ERROR))
            ->setTotalRounding(json_decode(Json::encode($context->getRounding()), true, 512, \JSON_THROW_ON_ERROR));

        $struct->setId(Struct::uuidToId($order->getId()));
        return $struct;
    }

    /**
     * @param OrderStruct $order
     * @param Context $context
     * @return string
     * @throws ExceptionImportOrder
     */
    private function getStateId(OrderStruct $order, Context $context): string
    {
        $orderConfig = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        if ($orderConfig->getOrderStateId()) {
            return $orderConfig->getOrderStateId();
        }
        $stateMachine = $this->stateMachineRegistry->getStateMachine(OrderStates::STATE_MACHINE, $context);
        /** @var StateMachineStateEntity $stateEntity */
        foreach ($stateMachine->getStates() as $stateEntity) {
            if ($stateEntity->getTechnicalName() === OrderStates::STATE_COMPLETED) {
                return $stateEntity->getId();
            }
        }
        throw new ExceptionImportOrder('can not found Order State Completed');
    }

    /**
     * @param OrderStruct $order
     * @return string
     */
    private function getSalesChannelId(OrderStruct $order): string
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        return $config->getSalesChannelId();
    }

    /**
     * @param OrderStruct $order
     * @param Context $context
     * @return string|null
     */
    private function getLanguageId(OrderStruct $order, Context $context): ?string
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        $locale = $order->getLocale();

        $criteria = new Criteria([$config->getSalesChannelId()]);
        $criteria->addAssociation('languages.locale');
        $criteria->addFilter(new EqualsFilter('active', true));
        $language = $this->repositorySalesChannel->search($criteria, $context)
            ->fmap(function (SalesChannelEntity $salesChannelEntity) use ($locale) {
                return $salesChannelEntity->getLanguages()->fmap(function (LanguageEntity $languageEntity) use ($locale) {
                    if ($languageEntity->getLocale()->getCode() === $locale) {
                        return $languageEntity->getId();
                    }
                });
            });
        if ($language) {
            return reset(array_values($language)[0]);
        }
        return null;
    }

    /**
     * @param OrderStruct $order
     * @return string
     */
    private function getOrderNumber(OrderStruct $order): string
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        return $this->numberRangeValueGenerator->getValue(
            OrderDefinition::ENTITY_NAME,
            Context::createDefaultContext(),
            $config->getSalesChannelId()
        );
    }

    /**
     * @param OrderStruct $order
     * @param Context $context
     * @return ImportOrderItemCollection
     * @throws ExceptionImportOrder
     */
    private function getLineItems(OrderStruct $order, Context $context): ImportOrderItemCollection
    {
        $collection = new ImportOrderItemCollection();
        /** @var OrderItemStruct $orderItemStruct */
        foreach ($order->getOrderItems() as $orderItemStruct) {
            if ($orderItemStruct->getType() == $orderItemStruct::ORDER_ITEM) {
                $importOrderItemCollection = $this->importOrderItemService->getOrderImportItemCollection($orderItemStruct, $order, $context);
                /** @var ImportOrderItemStruct $importOrderItemStruct */
                foreach ($importOrderItemCollection as $importOrderItemStruct) {
                    $collection->add($importOrderItemStruct);
                }
            }
        }
        return $collection;
    }

    /**
     * @return Context
     */
    private function makeContext(): Context
    {
        $context = Context::createDefaultContext();
        $context->setRounding(new CashRoundingConfig(2, 0.01, true));
        return $context;
    }

    /**
     * @param OrderStruct $order
     * @param Context $context
     * @return ImportOrderAddressCollection
     */

    private function makeAddressesCollection(OrderStruct $order, Context $context): ImportOrderAddressCollection
    {
        $collection = new ImportOrderAddressCollection();

        // Billing Address
        $billing = $this->importOrderAddressService->convertAddressStruct($order->getBillingAddress());

        // generate new UUID for the Billing Address
        $billing->setId(Uuid::randomHex());

        $collection->add($billing);
        $collection->setBillingAddress($billing);

        // Shipping Address
        $shipping = $this->importOrderAddressService->convertAddressStruct($order->getShippingAddress());

        // generate new UUID for the Shipping Address
        $shipping->setId(Uuid::randomHex());

        $collection->add($shipping);
        $collection->setShippingAddress($shipping);

        return $collection;
    }

    /**
     * @param OrderStruct $order
     * @param ImportOrderAddressStruct $billingAddress
     * @param Context $context
     * @return ImportOrderCustomerStruct
     */
    private function makeCustomerStruct(OrderStruct $order, ImportOrderAddressStruct $billingAddress, Context $context): ImportOrderCustomerStruct
    {
        $struct = new ImportOrderCustomerStruct();
        $struct->setSalutationId($billingAddress->getSalutationId());
        $struct->setLastName($billingAddress->getLastName());
        $struct->setFirstName($billingAddress->getFirstName());
        $struct->setEmail($order->getCustomerEmail());
        return $struct;
    }

    /**
     * @param OrderStruct $order
     * @return void
     * @throws ClientException
     */
    private function setExportedInZalando(OrderStruct $order): void
    {
        $struct = new OrderSetStruct();
        $struct->setMerchantOrderId($order->getMerchantId())
            ->setId($order->getId());
        $response = $this->apiZalandoOrderService->saveOrder($struct);
        $this->logger->info('Order Response ID:' . $order->getId(), ['content' => $response->getContentArray(), 'responseStatus' => $response->getStatus()]);
    }

    private function addLanguageInContext(OrderStruct $order, ?Context $context): void
    {
        $languageIdChain = $context->getLanguageIdChain();
        $languageId = $this->getLanguageId($order, $context);
        if ($languageId) {
            array_unshift($languageIdChain, $languageId);
        }
        $context->assign(['languageIdChain' => $languageIdChain]);
    }

    private function dispatch(OrderStruct $order, Context $context): void
    {
        $config = $this->configService->getImportOrderConfigBySalesChannelId($order->getSalesChannelId());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), $config->getSalesChannelId());
        $salesChannelContext->assign(['context' => $context]);

        $criteria = new Criteria([Struct::uuidToId($order->getId())]);
        $criteria->setTitle('order-route::order-loading');
        $criteria
            ->addAssociation('orderCustomer.salutation')
            ->addAssociation('deliveries.shippingMethod')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('deliveries.shippingOrderAddress.countryState')
            ->addAssociation('transactions.paymentMethod')
            ->addAssociation('lineItems.cover')
            ->addAssociation('lineItems.downloads.media')
            ->addAssociation('currency')
            ->addAssociation('addresses.country')
            ->addAssociation('addresses.countryState')
            ->addAssociation('stateMachineState')
            ->addAssociation('deliveries.stateMachineState')
            ->addAssociation('transactions.stateMachineState')
            ->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));

        $this->eventDispatcher->dispatch(new OrderPlacedCriteriaEvent($criteria, $salesChannelContext));

        $orderEntity = $this->repositoryOrder->search($criteria, $context)->first();

        $event = new OrderPlacedEvent(
            $context,
            $orderEntity,
            $config->getSalesChannelId()
        );

        $this->eventDispatcher->dispatch($event);
    }
}