<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

use Doctrine\DBAL\Connection;
use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;
use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Product\DataAbstractionLayer\StockUpdater;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\Product\Stock\StockAlteration;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Shopware\Core\System\Tax\TaxCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

MLFilesystem::gi()->loadClass('Shopware6_Helper_Model_ShopOrder');

class ML_Shopware66_Helper_Model_ShopOrder extends ML_Shopware6_Helper_Model_ShopOrder {

    /**
     * In new changes of Shopware 6 there is no need to this function. Maybe in future it will be useful.
     * stock reduction will be done depends on state of order
     * @param $sFromStateId
     * @throws Exception
     */
    protected function stockManagement($sFromStateId): void {
        $OrderStateMachineStateEntity =MLShopware6Alias::getRepository('state_machine_state.repository')->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('id', $this->oOrder->getShopOrderObject()->getStateId()))
            , Context::createDefaultContext()
        )->getEntities()->first();
        $OrderStateMachineStateTechnicalNameTechnicalName = $OrderStateMachineStateEntity->getTechnicalName();
        $this->handleStatusEvents($this->aNewData, $OrderStateMachineStateTechnicalNameTechnicalName);
        $context = $this->getContextWithOrderParameter($this->aNewData);
        $event = new StateMachineTransitionEvent(
            'order',
            $this->oOrder->getShopOrderObject()->getId(),
            MLShopware6Alias::getRepository('state_machine_state')->search(new Criteria([$sFromStateId]), Context::createDefaultContext())->first(),
            $OrderStateMachineStateEntity,
            $context
        );
        MagnalisterController::getStockUpdater()->stateChanged($event);
        //MagnalisterController::getStockStorage()->index(array($this->oOrder->getShopOrderObject()->getId()),Context::createDefaultContext());
        //MagnalisterController::getStockStorage()->alter(array($this->oOrder->getShopOrderObject()->getId()),Context::createDefaultContext());

    }


    /**
     * @param array $aData
     * @see \Shopware\Core\Content\Flow\Dispatching\FlowDispatcher::callFlowExecutor
     */
    protected function handleOrderEvents($aData) {
        $oConfig = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.shopware6flowskipped');
        $sEscape = $oConfig->get('value');
        if ($sEscape === '1') {
            return;
        }
        $oConfig->set('value', 1)->save();
        try {
            $context = $this->getContextWithOrderParameter($aData);
            $OrderFixedQuantity = $this->oOrder->getShopOrderObject();
            $event = new CheckoutOrderPlacedEvent($context, $OrderFixedQuantity, $this->getSalesChannel()->getId());
            /** @var Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher $eventDispatcher */
            $eventDispatcher = MagnalisterController::getShopwareMyContainer()->get('event_dispatcher');
            $eventDispatcher->dispatch($event);
        } catch (\Throwable $ex) {
            MLMessage::gi()->addDebug($ex);
            MLLog::gi()->add(MLSetting::gi()->data('sCurrentOrderImportLogFileName'), array(
                'MOrderId'              => MLSetting::gi()->data('sCurrentOrderImportMarketplaceOrderId'),
                'Shopware Events Error' => 'Shopware Event/Flow cannot be executed',
                'Error Info'            => array(
                    'Error message' => $ex->getMessage(),
                    'Error trace'   => $ex->getTraceAsString(),
                )
            ));
        }
        $oConfig->set('value', 0)->save();
    }

    /**
     * it is not used
     * Testing function to change and update status of order and product stock manually
     * $lineItem = $this->oOrder->getShopOrderObject()->getLineItems();
     */
    public function StockReduction( $lineItem, Context $context, $OrderStatus = OrderStates::STATE_COMPLETED ): void
    {
        /*$OrderFixedQuantity = $this->oOrder->getShopOrderObject();
        foreach ($OrderFixedQuantity->getLineItems() as $lineItem) {
                      $lineItem->setQuantity(0);
        }*/
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }
        if($OrderStatus == OrderStates::STATE_COMPLETED){
            echo print_m($OrderStatus);
            $sql = <<<'SQL'
                    UPDATE product
                    SET stock = stock + :quantity, sales = sales - :quantity, available_stock = stock
                    WHERE id = :id AND version_id = :version
                SQL;
        }elseif ($OrderStatus == OrderStates::STATE_IN_PROGRESS){

        }elseif ($OrderStatus == OrderStates::STATE_OPEN){

        }
        $query = new RetryableQuery(
            $this->getShopwareDb(),
            $this->getShopwareDb()->prepare($sql)
        );
        $query->execute([
            'quantity' => $lineItem->getQuantity(),
            'id' => Uuid::fromHexToBytes($lineItem->getReferencedId()),
            'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
        ]);

    }



}

