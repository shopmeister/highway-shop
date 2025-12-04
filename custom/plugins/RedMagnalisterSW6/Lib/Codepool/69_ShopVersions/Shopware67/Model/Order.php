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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryEntity;

MLFilesystem::gi()->loadClass('Shopware6_Model_Order');

class ML_Shopware67_Model_Order extends ML_Shopware6_Model_Order {

    /**
     * return uuid of current order state
     * @return string|null
     */
    public function getShopOrderStatus(): ?string {
        try {
            return $this->getStateMachineStateEntity($this->getShopOrderObject()->getStateId())->getTechnicalName();
        } catch (Exception $oExc) {
            return null;
        }
    }

    /**
     *
     * @return string|null
     */
    public function getShopOrderStatusName() {
        try {
            return $this->getStateMachineStateEntity($this->getShopOrderObject()->getStateId())->getName();
        } catch (Exception $oExc) {
            return null;
        }
    }

    public function getShopPaymentStatus() {
        try {
            if ($this->getShopOrderObject()->getTransactions()->first() !== null) {
                return $this->getStateMachineStateEntity($this->getShopOrderObject()->getTransactions()->first()->getStateId())->getTechnicalName();
            }
        } catch (Exception $oExc) {
            MLMessage::gi()->addDebug($oExc);
        }
        return null;
    }

    public function getShippingDateTime() {
        $date = $this->getShopOrderHistoryLastChangeDate();
        if ($date === null) {
            $date = $this->getShopOrderLastChangedDate();
        }
        return $date;
    }

    public function getStateMachineStateEntity($StateID) {
        $OrderStateMachineStateEntity = MLShopware6Alias::getRepository('state_machine_state.repository')->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('id', $StateID))
            , Context::createDefaultContext()
        )->getEntities()->first();
        return $OrderStateMachineStateEntity;
    }

    protected function getShopOrderHistoryLastChangeDate() {
        $mTime= null;
        try {
            /** @var StateMachineHistoryEntity $oHistory */
            $oHistory = MLShopware6Alias::getRepository('state_machine_history')
                ->search(
                    (new Criteria())
                        ->addFilter(new EqualsFilter('entityName', OrderDefinition::ENTITY_NAME))
                        ->addFilter(new EqualsFilter('referencedId', $this->get('current_orders_id')))
                        ->addSorting(new FieldSorting('createdAt', 'DESC'))
                    , Context::createDefaultContext())->first();
            $mTime = $oHistory === null ? null : $oHistory->getCreatedAt()->format('Y-m-d H:i:s');

        } catch (Exception $oEx) {
            MLMessage::gi()->addDebug($oEx);
        }
        return $mTime;
    }
}
