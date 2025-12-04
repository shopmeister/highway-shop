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
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
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

MLFilesystem::gi()->loadClass('Shop_Helper_Model_ShopOrder_Abstract');

class ML_Shopware6_Helper_Model_ShopOrder extends ML_Shop_Helper_Model_ShopOrder_Abstract {

    /**
     * @var  $oExistingOrder OrderEntity
     */
    protected $oExistingOrder = null;

    /**
     * @var array $aNewProduct
     */
    protected $aNewProduct = array();

    /**
     * @var OrderEntity $oNewOrder
     */
    protected $oNewOrder = null;

    /**
     * @var ML_Shopware6_Model_Order $oOrder
     */
    protected $oOrder = null;

    /**
     * @var float
     */
    protected $fTotalAmount = 0.00;

    /**
     * @var float
     */
    protected $fTotalAmountNet = 0.00;

    /**
     * @var float
     */
    protected $fTotalProductAmount = 0.00;

    /**
     * @var CalculatedTaxCollection
     */
    protected $oTotalCalculatedTaxCollection;

    /**
     * @var TaxRuleCollection
     */
    protected $oTotalTaxRuleCollection;

    /**
     * @var CalculatedTaxCollection
     */
    protected $oShippingCalculatedTaxCollection;

    /**
     * @var float
     */
    protected $fMaxProductTax;

    /**
     * Position of order line item
     * @var int
     */
    protected $productLastPosition = 0;

    protected $aCuttedField = [];
    protected $aItemLines = [];
    /**
     * set oder object in initializing the order helper
     * @param ML_Shopware6_Model_Order $oOrder
     * @return ML_Shopware6_Helper_Model_ShopOrder
     * @throws Exception
     */
    public function setOrder($oOrder) {
        $this->aCuttedField = [];
        $this->aItemLines = [];
        $this->fTotalAmount = 0.00;
        $this->fTotalAmountNet = 0.00;
        $this->fMaxProductTax = 0.00;
        $this->productLastPosition = 0;
        $this->oOrder = $oOrder;
        $this->oExistingOrder = null;
        if ($this->oOrder->exists() && $this->oOrder->existsInShop()) {
            $this->oExistingOrder = $oOrder->getShopOrderObject();
        }
        $this->aExistingOrderData = $oOrder->get('orderdata');
        return $this;
    }

    /**
     * initializing order import and update
     * @return array
     * @throws Exception
     */
    public function shopOrder(): array {
        if ($this->aExistingOrderData === null || count($this->aExistingOrderData) == 0) {
            $aReturnData = $this->createUpdateOrder();
        } elseif (!is_object($this->oExistingOrder)) {// if order doesn't exist in shop  we create new order
            $this->aNewData = MLHelper::gi('model_service_orderdata_merge')->mergeServiceOrderData($this->aNewData, $this->aExistingOrderData, $this->oOrder);
            $aReturnData = $this->createUpdateOrder();
        } else {//update order if exist
            if ($this->checkForUpdate()) {
                $this->aNewData = MLHelper::gi('model_service_orderdata_merge')->mergeServiceOrderData($this->aNewData, $this->aExistingOrderData, $this->oOrder);
                $this->updateOrder();
                $aReturnData = $this->aNewData;
            } else {
                $this->aNewProduct = $this->aNewData['Products'];
                $this->aNewData = MLHelper::gi('model_service_orderdata_merge')->mergeServiceOrderData($this->aNewData, $this->aExistingOrderData, $this->oOrder);
                $aReturnData = $this->createUpdateOrder();
            }
        }
        return $aReturnData;
    }

    /**
     * Only if shipping method name or payment method name or order, payment status is changed
     * @return ML_Shopware6_Helper_Model_ShopOrder
     * @throws Exception
     */
    public function updateOrder(): \ML_Shopware6_Helper_Model_ShopOrder {
        $sFromStateId = $this->oOrder->getShopOrderObject()->getStateId();
        /*
         * By using orderStateTransition, some state transitions are not allowed and it could prevent to change order state
         */
        //        MagnalisterController::getOrderService()->orderStateTransition($sOrderId, $this->aNewData['Order']['Status'], new ParameterBag(), Context::createDefaultContext());

        $orderData = [
            'id' => $this->oExistingOrder->getId(),
        ];
        if ($this->oOrder->getUpdatableOrderStatus()) {

            $orderData['stateId'] = $this->getOrderStateId($this->aNewData['Order']['Status'], OrderStates::STATE_MACHINE);
            $orderData['deliveries'] = [
                [
                    'id'               => $this->getExistingDeliveryId(),
                    'shippingMethodId' => $this->getShippingMethod(),
                ],
            ];
        }
        if ($this->oOrder->getUpdatablePaymentStatus()) {
            $configPaymentStatus = $this->getPaymentStatus();
            $orderData['transactions'][] =
                [
                    'id'              => $this->oExistingOrder->getTransactions()->first()->getId(),
                    'paymentMethodId' => $this->getPaymentMethodId(),
                    'stateId'         => $this->getOrderStateId($configPaymentStatus, OrderTransactionStates::STATE_MACHINE),
                ];

        }
        //Kint::dump($orderData);
        if ($this->oOrder->getUpdatableOrderStatus() || $this->oOrder->getUpdatablePaymentStatus()) {
            $this->executeWithTransaction(function() use ($orderData) {
                MLShopware6Alias::getRepository('order')
                    ->update([$orderData], Context::createDefaultContext());
            }, 'order status/payment update');
            $this->stockManagement($sFromStateId);
        }


        return $this;
    }

    /**
     * create a new order by magnalister order data
     * @return array
     * @throws Exception
     * @see \Shopware\Core\Framework\Test\DataAbstractionLayer\Dbal\EntityForeignKeyResolverTest::createOrder
     */
    public function createUpdateOrder() {
        try {
            $aData = $this->aNewData;
            $aAddresses = $aData['AddressSets'];

            if (empty($aAddresses['Main'])) {// add new order when Main address is filled
                throw new Exception('main address is empty');
            }

            if (count($aData['Products']) <= 0) {// add new order when order has any product
                throw new Exception('product is empty');
            }

            /**
             * @var $oCurrency CurrencyEntity
             */
            $oCurrency = MLShopware6Alias::getRepository('currency.repository')->search((new Criteria())->addFilter(new EqualsFilter('isoCode', $aData['Order']['Currency'])), Context::createDefaultContext())->getEntities()->first();
            if (!is_object($oCurrency)) {
                $sMessage = MLI18n::gi()->get('Orderimport_CurrencyCodeDontExistsError', array(
                        'mpOrderId' => MLSetting::gi()->get('sCurrentOrderImportMarketplaceOrderId'),
                        'ISO'       => $aData['Order']['Currency']
                    )
                );
                MLErrorLog::gi()->addError(0, ' ', $sMessage, array('MOrderID' => MLSetting::gi()->get('sCurrentOrderImportMarketplaceOrderId')));
                throw new Exception($sMessage);
            }
            //show  in order detail
            $sInternalComment = isset($aData['MPSpecific']['InternalComment']) ? $aData['MPSpecific']['InternalComment'] : '';
            //show in order detail and invoice pdf
            $sCustomerComment = '';
            if (MLModule::gi()->getConfig('order.information')) {
                $sCustomerComment .= isset($aData['MPSpecific']['InternalComment']) ? $aData['MPSpecific']['InternalComment'] : '';
            }

            $iPaymentID = $this->getPaymentMethodId();
            $aBillingAddress = $this->getAddress($aData['AddressSets'], 'Billing');
            $aShippingAddress = $this->getAddress($aData['AddressSets'], 'Shipping');
            $fMaxTaxRate = $this->getTaxRate();
            $oTaxCalculator = new TaxCalculator();
            $oTaxRuleCollection = new TaxRuleCollection([new TaxRule($this->fMaxProductTax)]);
            $oCustomer = $this->getCustomer();

            $this->aItemLines = $this->addProductsAndTotals();
            //$fTotalPrice = (float)$aData['Order']['TotalPrice'];
            $oShippingCost = $this->getShippingCost();
            $configPaymentStatus = $this->getPaymentStatus();
            if (count($this->aCuttedField) > 0) {
                $sInternalComment .= "\n".'Truncated fields:'."\n";
            }

            foreach ($this->aCuttedField as $sFiledName => $sFieldValue) {
                $sInternalComment .= 'Original value of "'.$sFiledName.'":'.$sFieldValue."\n";
            }
            $blNewDelivery = $this->oExistingOrder === null || $this->oExistingOrder->getDeliveries() === null || $this->oExistingOrder->getDeliveries()->first() === null;
            $trackingCodes = $blNewDelivery ? [] : $this->oExistingOrder->getDeliveries()->first()->getTrackingCodes();
            if (isset($aData['MPSpecific']['Trackingcode']) && !in_array($aData['MPSpecific']['Trackingcode'], $trackingCodes)) {
                $trackingCodes[] = $aData['MPSpecific']['Trackingcode'];
            }
            $this->fTotalAmountNet = round($this->fTotalAmountNet, 2);
            $oCartPrice = new CartPrice($this->fTotalAmountNet, $this->fTotalAmount, $this->fTotalProductAmount, $this->getTotalCalculatedTaxCollection(), $this->oTotalTaxRuleCollection, CartPrice::TAX_STATE_GROSS);
            $deliveryId = $this->oExistingOrder === null ? Uuid::randomHex() : $this->getExistingDeliveryId();
            $TransactionId =$this->oExistingOrder === null ? Uuid::randomHex() : $this->getExistingTranactionId();
            $orderData = [
                'id'              => $this->oExistingOrder === null ? Uuid::randomHex() : $this->oExistingOrder->getId(),
                'orderNumber'     => $this->getShopwareOrderNumber(),
                'currencyId'      => $oCurrency->getId(),
                'languageId'      => $this->getSalesChannel()->getLanguageId(), //Defaults::LANGUAGE_SYSTEM use default from SalesChannel
                'deepLinkCode'    => Random::getBase64UrlString(32),
                'salesChannelId'  => $this->getSalesChannel()->getId(),
                'currencyFactor'  => $oCurrency->getFactor(),
                'stateId'         => $this->getOrderStateId($aData['Order']['Status'], OrderStates::STATE_MACHINE),
                'price'           => $oCartPrice,
                'shippingCosts'   => $oShippingCost,
                'customerComment' => $sInternalComment,
                'orderCustomer'   => [
                    'id'             => $this->oExistingOrder === null ? Uuid::randomHex() : $this->oExistingOrder->getOrderCustomer()->getId(),
                    'customerId'     => $oCustomer->getId(),
                    'salutationId'   => $oCustomer->getSalutationId(),
                    'email'          => $oCustomer->getEmail(),
                    'firstName'      => $oCustomer->getFirstName(),
                    'lastName'       => $oCustomer->getLastName(),
                    'customerNumber' => $oCustomer->getCustomerNumber(),
                    'vatIds' => $oCustomer->getVatIds(),
                ],
                'transactions'    => [
                    [
                        /**
                         * @see \Shopware\Core\Checkout\Cart\Processor::process
                         * @see \Shopware\Core\Checkout\Cart\Transaction\TransactionProcessor::process
                         * @see \SwagMigrationAssistant\Profile\Shopware\Converter\OrderConverter::getTaxRules
                         */
                        'id'              => $TransactionId,
                        'amount'          =>
                            new CalculatedPrice(
                                $this->fTotalAmount,
                                $this->fTotalAmount,
                                $oTaxCalculator->calculateGrossTaxes($this->fTotalAmount, $oTaxRuleCollection),
                                $oTaxRuleCollection
                            ),
                        'paymentMethodId' => $iPaymentID,
                        'stateId'         => $this->getOrderStateId($configPaymentStatus, OrderTransactionStates::STATE_MACHINE),

                    ]
                ],
                'lineItems'       => $this->aItemLines,
                'deliveries'      => [
                    [
                        'id'                     => $deliveryId,
                        'shippingOrderAddressId' => $blNewDelivery || $this->isNewAddress() ? $aShippingAddress['id'] : $this->oExistingOrder->getDeliveries()->first()->getShippingOrderAddressId(),
                        'shippingMethodId'       => $this->getShippingMethod(),
                        'stateId'                => $blNewDelivery ? $this->getOrderStateId(OrderDeliveryStates::STATE_OPEN, OrderDeliveryStates::STATE_MACHINE) : $this->oExistingOrder->getDeliveries()->first()->getStateId(),
                        'trackingCodes'          => $trackingCodes,
                        'shippingDateEarliest'   => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                        'shippingDateLatest'     => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                        'shippingCosts'          => $oShippingCost,
                        'positions'              => $this->getDeliveryPosition($this->aItemLines),
                    ],
                ],
            ];

            // new field since shopware 6.7.1+
            if (version_compare(MLSHOPWAREVERSION, '6.7.1.0', '>=')) {//filling this fields will possible to filter the orders by delivery id and payment status
                $orderData['primaryOrderDeliveryId'] = $deliveryId;
                $orderData['primaryOrderTransactionId'] = $TransactionId;
            }

            $orderData = $this->addCustomFields($orderData);
            if (version_compare(MLSHOPWAREVERSION, '6.4.0.0', '>=')) {
                $orderData['itemRounding'] = json_decode(json_encode(new Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig(2, 0.01, true)), true);
                $orderData['totalRounding'] = json_decode(json_encode(new Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig(2, 0.01, true)), true);
            }

            if ($this->isNewAddress() || $this->oExistingOrder === null) {
                $orderData['orderDateTime'] = (new \DateTime($aData['Order']['DatePurchased']))->format(Defaults::STORAGE_DATE_TIME_FORMAT);
                $orderData['billingAddressId'] = $aBillingAddress['id'];
                $orderData['addresses'] = [
                    $aBillingAddress,
                    $aShippingAddress,
                ];
            } else {
                $orderData['billingAddressId'] = $this->oExistingOrder->getBillingAddressId();
            }

            if ($this->oExistingOrder === null) {
                $this->executeWithTransaction(function() use ($orderData) {
                    MLShopware6Alias::getRepository('order')
                        ->create([$orderData], Context::createDefaultContext());
                }, 'order creation');
            } else {
                $this->executeWithTransaction(function() use ($orderData) {
                    MLShopware6Alias::getRepository('order')
                        ->update([$orderData], Context::createDefaultContext());
                }, 'order update');
            }

            $this->oOrder->set('orders_id', $orderData['orderNumber']);//important to show order number in backoffice of magnalister
            $this->oOrder->set('current_orders_id', $orderData['id']); //important to find order in shopware database
            if ($this->oExistingOrder === null) {
                $this->handleOrderEvents($aData);
            }

            $this->stockManagement($this->getOrderStateId(OrderStates::STATE_OPEN, OrderStates::STATE_MACHINE));

        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
            throw $ex;
        }
        return $aData;
    }

    protected function addCustomFields($orderData) {
        return $orderData;
    }

    protected function getShippingCost() {
        $this->oShippingCalculatedTaxCollection = new CalculatedTaxCollection();
        $oTaxRuleCollection = new TaxRuleCollection([new TaxRule($this->getTaxRate())]);
        $oTaxCalculator = new TaxCalculator();
        $fMaxTaxRate = $this->getTaxRate();
        $fShippingCost = round((float)$this->getTotal('Shipping')['Value'], 2);
        $this->addTotalAmount($fShippingCost, MLPrice::factory()->calcPercentages($fShippingCost, null, $fMaxTaxRate));
        $this->oShippingCalculatedTaxCollection = $oTaxCalculator->calculateGrossTaxes($fShippingCost, $oTaxRuleCollection);
        $this->addTotalCalculatedTaxCollection($this->oShippingCalculatedTaxCollection->first());
        return new CalculatedPrice($fShippingCost, $fShippingCost, $this->oShippingCalculatedTaxCollection, $oTaxRuleCollection);
    }


    /**
     * @return string
     * @throws Exception
     */
    protected function getExistingTranactionId(): string {
        if ($this->oExistingOrder === null) {
            throw new Exception('Use this function only for merging and updating order');
        }
        $aExistingDelivery = MLShopware6Alias::getRepository('order_transaction')->search((new Criteria())
            ->addFilter(new EqualsFilter('orderId', $this->oExistingOrder->getId())), Context::createDefaultContext())
            ->getEntities()->first();
        return $aExistingDelivery->getId();
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getExistingDeliveryId(): string {
        if ($this->oExistingOrder === null) {
            throw new Exception('Use this function only for merging and updating order');
        }
        $aExistingDelivery = MLShopware6Alias::getRepository('order_delivery')->search((new Criteria())
            ->addFilter(new EqualsFilter('orderId', $this->oExistingOrder->getId())), Context::createDefaultContext())
            ->getEntities()->first();
        return $aExistingDelivery->getId();
    }

    /**
     * This functions is only implemented to prevent errors for duplicated of code
     * @return array
     */
    protected function addProductsAndTotals(): array {
        $fOtherAmount = $this->fTotalAmount;
        $this->oTotalTaxRuleCollection = new TaxRuleCollection();

        $aProducts = $this->getProductArray($this->aNewData['Products']);
        $aTotalProducts = [];
        // add orders totals
        foreach ($this->aNewData['Totals'] as &$aTotal) {
            switch ($aTotal['Type']) {
                case 'Shipping':
                {
                    //it is already managed in caller functions
                    break;
                }
                case 'Payment':
                default:
                {
                    if ((float)$aTotal['Value'] !== 0.0) {
                        $aTotalProducts[] = [
                            'SKU'        => $aTotal['SKU'] ?? '',
                            'ItemTitle'  => (isset($aTotal['Code']) && $aTotal['Code'] !== '') ? $aTotal['Code'] : $aTotal['Type'],
                            'Quantity'   => 1,
                            'Price'      => $aTotal['Value'],
                            'Tax'        => array_key_exists('Tax', $aTotal) ? $aTotal['Tax'] : false,
                            'ForceMPTax' => false
                        ];

                    }
                    break;
                }
            }
        }
        $aAllItems = array_merge($this->getProductArray($aTotalProducts), $aProducts);
        $this->fTotalProductAmount = $this->fTotalAmount - $fOtherAmount;
        return $aAllItems;
    }

    protected function getProductArray($aProducts) {
        $aItems = [];
        $aExistingProducts = [];
        if ($this->oExistingOrder !== null) {
            $aExistingProducts = MLShopware6Alias::getRepository('order_line_item')->search((new Criteria())
                ->addFilter(new EqualsFilter('orderId', $this->oExistingOrder->getId())), Context::createDefaultContext())
                ->getEntities();
        }
        foreach ($aProducts as &$aProduct) {
            $aProduct['SKU'] = isset($aProduct['SKU']) ? $aProduct['SKU'] : ' ';
            $oProduct = MLShopware6Alias::getProductModel()->getByMarketplaceSKU($aProduct['SKU']);
            $blFound = false;
            $iProductQuantity = (int)$aProduct['Quantity'];
            $fProductPrice = (float)$aProduct['Price'];

            if ($this->oExistingOrder !== null) {
                if (count($aExistingProducts) > 0) {
                    // Set once productLastPosition to count of existing products in order
                    if ($this->productLastPosition === 0) {
                        $this->productLastPosition = count($aExistingProducts);
                    }
                    foreach ($aExistingProducts as $sKey => $item) {
                        if ($item->getQuantity() === $iProductQuantity && ($item->getUnitPrice() === $fProductPrice)) {
                            if (trim($aProduct['SKU']) === '' && ($item->getLabel() === $aProduct['ItemTitle'] || $item->getLabel() === $aProduct['ItemTitle'].'('.$aProduct['SKU'].')')) {//in ebay sku could be empty
                                $blFound = true;
                            } else if (trim($aProduct['SKU']) !== '') {
                                if (isset($item->getPayload()['productNumber'])) {
                                    if ($item->getPayload()['productNumber'] === $aProduct['SKU']) {
                                        $blFound = true;
                                    } else {// perhaps sku have changed or wrong key type of total?
                                        $sSWProductNumber = $item->getPayload()['productNumber'];
                                        if ($oProduct->exists() && $oProduct->getSku() === $sSWProductNumber) {
                                            $blFound = true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($blFound) {
                            $aExistingProducts->remove($sKey);
                            break;
                        }
                    }
                }
            }
            $aItem = [
                'id' => Uuid::randomHex(),
            ];
            $mMarketplaceTax = ($aProduct['Tax'] !== false && array_key_exists('ForceMPTax', $aProduct) && $aProduct['ForceMPTax']) ? (float)$aProduct['Tax'] : null;
            if ($oProduct->exists()) {
                /** @var SalesChannelProductEntity|null $product */
                $sProductId = $oProduct->getCorrespondingProductEntity()->getId();
                $oProductEntity = $oProduct->getCorrespondingProductEntity();
                try {
                $oSalesChannelContext = MagnalisterController::getSalesChannelContextFactory()
                    ->create($sProductId, $this->getSalesChannel()->getId());
                $ProductCriteria = new Criteria();
                $ProductCriteria->addAssociations(['options','options.group'])
                    ->addFilter(new EqualsFilter('id', $sProductId));
                $product = MLShopware6Alias::getRepository('sales_channel.product')
                    ->search($ProductCriteria, $oSalesChannelContext)
                    ->get($sProductId);
                } catch (\Throwable $ex) {
                    $product = null;
                }
                $fProductTaxRate = $mMarketplaceTax ?? $oProduct->getTax($this->aNewData['AddressSets']);
                if (!is_object($product)) {
                    $product = $oProductEntity;
                }
                $payload = [
                    'isNew' => method_exists($product, 'isNew') ? $product->isNew() : null,
                    'isCloseout' => $product->getIsCloseout(),
                    'customFields' => $product->getCustomFields(),
                    'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT) : null,
                    'releaseDate' => $product->getReleaseDate() ? $product->getReleaseDate()->format(Defaults::STORAGE_DATE_TIME_FORMAT) : null,
                    'markAsTopseller' => $product->getMarkAsTopseller(),
                    'productNumber' => $product->getProductNumber(),
                    'manufacturerId' => $product->getManufacturerId(),
                    'taxId' => $product->getTaxId(),
                    'tagIds' => $product->getTagIds(),
                    'categoryIds' => $product->getCategoryTree(),
                    'propertyIds' => $product->getPropertyIds(),
                    'optionIds' => $product->getOptionIds(),
                    'options' => $product->getVariation(),
                ];

                $aItem['payload'] = $payload;

                //                $aItem['coverId'] = $product->getCoverId();
                $aItem['productId'] = $oProduct->get('ProductsId');
                $aItem['identifier'] = $aItem['productId'];
                $aItem['referencedId'] = $aItem['productId'];
                if ($aProduct['StockSync']) {
                    $aItem['type'] = 'product';//that is useful for existing product in shop, to show link of product in order detail, other possible values: product, credit, custom, promotion
                } else {
                    $aItem['type'] = 'custom';//To pervent reduction in availibles tock on product
                }
            } else {
                $fProductTaxRate = $mMarketplaceTax ?? $this->getFallbackTax();
                $sIdentifier = Uuid::randomHex();
                $aItem['identifier'] = $sIdentifier;
                $aItem['referencedId'] = $sIdentifier;
                $aItem['payload'] = [
                    'productNumber' => $aProduct['SKU'],
                ];
                $aItem['type'] = 'custom';//If product doesn't exist in shop we couldn't use "product" as type, possible values: product, credit, custom, promotion
            }
            $aItem['quantity'] = $iProductQuantity;

            $aItem['label'] = $aProduct['ItemTitle'].($aItem['type'] === 'custom' ? '('.$aProduct['SKU'].')' : '');
            //More than 255 character is not allowed for order label in Shopware 6
            if (strlen($aItem['label']) > 255) {
                $aItem['label'] = mb_substr($aItem['label'], 0, 255 - 3, 'UTF-8').'...';
            }
            [$aItem['price'], $aItem['priceDefinition']] = $this->getProductPrice($fProductPrice, $iProductQuantity, $fProductTaxRate);

            if (!$blFound) {
                //Position of item (used same logic like shopware 6) increase before usage
                $aItem['position'] = ++$this->productLastPosition;

                $aItems[] = $aItem;
            }
        }
        return $aItems;
    }

    protected function addTotalAmount($fGrossAmount, $fNetAmount): void {
        $this->fTotalAmount += $fGrossAmount;
        $this->fTotalAmountNet += $fNetAmount;
    }

    /**
     * @return float Rate
     * @throws Exception
     */
    protected function getTaxRate(): float {
        $fMaxProductTax = null;
        $fDefaultProductTax = 0.00;
        if (!empty($this->aItemLines)) {
            foreach ($this->aItemLines as $item) {
                foreach ($item['price']->getCalculatedTaxes()->getIterator() as $oTax) {
                    $fMaxProductTax = max($this->fMaxProductTax, $oTax->getTaxRate());
                }
            }
        }
        if ($this->oExistingOrder !== null && $this->oExistingOrder->getLineItems() !== null) {
            foreach ($this->oExistingOrder->getLineItems()->getIterator() as $item) {
                foreach ($item->getPrice()->getCalculatedTaxes()->getIterator() as $oTax) {
                    $fMaxProductTax = max($this->fMaxProductTax, $oTax->getTaxRate());
                }
            }
        }
        if ($fMaxProductTax !== null) {
            $this->fMaxProductTax = $fMaxProductTax;
        } else {
            // fallback
            $fDefaultProductTax = $this->getFallbackTax();
        }
        //        $sTotalTaxClassId = MLShopware6Alias::getRepository('tax')->searchIds((new Criteria())->addFilter(new EqualsFilter('taxRate', (float)$fDefaultProductTax)), Context::createDefaultContext())->firstId();

        //        if ($sTotalTaxClassId === null) {
        //            throw new Exception('Tax class cannot be found for tax rate:'.$fDefaultProductTax);
        //        }
        return max((float)$fDefaultProductTax, $this->fMaxProductTax);

    }

    /**
     * Try to find customer in Shopware by email. if it doesn't exists, it creates new customer, at the end it returns customer id
     * @return CustomerEntity new created or existing customer entity
     * @throws Exception
     * @see \Shopware\Storefront\Test\Controller\CheckoutControllerTest::createCustomer
     */
    protected function getCustomer(): CustomerEntity {
        $blNewCustomer = false;
        $aAddress = $this->aNewData['AddressSets']['Main'];
        $sCustomerId = $this->findCustomerByEmail();
        if ($sCustomerId === null) {
            $blNewCustomer = true;
            $sCustomerId = Uuid::randomHex();
        }
        $sSalutationId = $this->getSalutationId($aAddress['Gender']);
        $mPaymentMethodId = $this->getPaymentMethodId();
        $mDefaultShippingAddress = $this->getAddress($this->aNewData['AddressSets'], 'Shipping');
        $mDefaultBillingAddress = $this->getAddress($this->aNewData['AddressSets'], 'Billing');
        $sConfigCustomerGroup = $this->getCustomerGroup();

        if ($aAddress['Firstname'] === '') {
            $aAddress['Firstname'] = '--';
        }
        if ($aAddress['Lastname'] === '') {
            $aAddress['Lastname'] = '--';
        }

        // VAT Ids needs to be an array since shopware 6 can handle multiple vat ids
        $vatIds = null;
        if (!empty($aAddress['UstId'])) {
            $vatIds = [$aAddress['UstId']];
        }

        $customer =
            [
                'id'                     => $sCustomerId,
                'salesChannelId'         => $this->getSalesChannel()->getId(),
                'defaultPaymentMethodId' => $mPaymentMethodId,
                'firstName'              => $aAddress['Firstname'],
                'lastName'               => $aAddress['Lastname'],
                'salutationId'           => $sSalutationId,
                'groupId'                => $sConfigCustomerGroup === '-' ? $this->getSalesChannel()->getCustomerGroupId() : $sConfigCustomerGroup,
                'vatIds'                 => $vatIds,
            ];

        // if company field is not empty set company for customer account so account type is then a business account
        // see also \Shopware\Core\Checkout\Customer\SalesChannel\RegisterRoute::register -> accountType
        if (!empty($aAddress['Company'])) {
            $customer['company'] = $aAddress['Company'];
            $customer['accountType'] = 'business';
        }

        $blNewShippingAddress = $this->checkForDuplicateAddress('customer', $mDefaultShippingAddress, $sCustomerId);
        $blNewBillingAddress = $this->checkForDuplicateAddress('customer', $mDefaultBillingAddress, $sCustomerId);


        if (($blNewCustomer || $this->isNewAddress() || $this->oExistingOrder === null)) {
            if ($blNewShippingAddress || $blNewCustomer) {
                // Retrieve default address ID only for existing customers
                // This ensures that when adding customer addresses, multiple address entries are avoided
                if (!$blNewCustomer) {
                    $sAddressId = $this->getCustomerDefaultAddressId($sCustomerId, 'shipping');
                    $mDefaultShippingAddress['id'] = !empty($sAddressId) ? $sAddressId : $mDefaultShippingAddress['id'];
                }
                $customer['defaultShippingAddress'] = $mDefaultShippingAddress;
            }
            if ($blNewBillingAddress || $blNewCustomer) {
                // Retrieve default address ID only for existing customers
                // This ensures that when adding customer addresses, multiple address entries are avoided
                if (!$blNewCustomer) {
                    $sAddressId = $this->getCustomerDefaultAddressId($sCustomerId, 'billing');
                    $mDefaultBillingAddress['id'] = !empty($sAddressId) ? $sAddressId : $mDefaultBillingAddress['id'];
                }
                $customer['defaultBillingAddress'] = $mDefaultBillingAddress;
            }
        }

        //        throw new Exception(__LINE__);
        $sError = '';
        try {
            if ($blNewCustomer) {

                $customer['email'] = $aAddress['EMail'];
                $customer['password'] = $this->generateNewPasseword();
                $customer['guest'] = $sConfigCustomerGroup === '-';
                $customer['customerNumber'] = MagnalisterController::getNumberRangeValueGenerator()->getValue(
                    MLShopware6Alias::getRepository('customer')->getDefinition()->getEntityName(),
                    Context::createDefaultContext(),
                    $this->getSalesChannel()->getId()
                );
                MLShopware6Alias::getRepository('customer.repository')->create([$customer], Context::createDefaultContext());
            } else {
                MLShopware6Alias::getRepository('customer.repository')->update([$customer], Context::createDefaultContext());
            }

        } catch (\Exception $ex) {
            $sError = $ex->getMessage();
        }
        //Kint::dump($customer);
        $oCustomer = MLShopware6Alias::getRepository('customer.repository')->search(
            (new Criteria([$sCustomerId]))->addAssociations(
                ['defaultShippingAddress.country', 'defaultShippingAddress.state', 'defaultBillingAddress.country']),
            Context::createDefaultContext())->getEntities()->first();
        if ($oCustomer instanceof CustomerEntity) {
            return $oCustomer;
        } else {
            throw new \Exception('Customer cannot be created: '.$sError);
        }

    }

    protected function generateNewPasseword() {
        $sPassword = "";
        for ($i = 0; $i < 12; $i++) {
            $iRandomNumber = function_exists('random_int') ? random_int(0, 35) : mt_rand(0, 35);
            if ($iRandomNumber < 10) {
                $sPassword .= $iRandomNumber;
            } else {
                $sPassword .= chr($iRandomNumber + 87);
            }
        }
        return $sPassword;
    }

    /**
     * Transforms address data format to the required format for shopware 6 order model.
     * @param $addressSets Array with all address sets, addressType decides if its shipping, billing or main
     * @param null|string $addressType
     * @return []|array
     * @throws Exception
     */
    protected function getAddress($addressSets, $addressType = null) {
        $aAddress = $addressSets[$addressType];
        $sCountryId = $this->getCountryId($aAddress['CountryCode']);
        $sSalutationId = $this->getSalutationId($aAddress['Gender']);
        $iStateId = $this->getStateId($aAddress['Suburb'], $sCountryId);
        $sCity = trim($aAddress['City']);
        if ($iStateId === null) {
            if (!empty($aAddress['Suburb'])) {
                $sCity .= ' - '.trim($aAddress['Suburb']);
            }
        }

        if ($aAddress['Firstname'] === '') {
            $aAddress['Firstname'] = '--';
        }
        if ($aAddress['Lastname'] === '') {
            $aAddress['Lastname'] = '--';
        }
        if ($aAddress['StreetAddress'] === '') {
            $aAddress['StreetAddress'] = '--';
        }

        if ($aAddress['Company'] == false) {
            $aAddress['Company'] = null;
        }

        if ($aAddress['AddressAddition'] == false) {
            $aAddress['AddressAddition'] = null;
        }
        if (empty($aAddress['Postcode'])) {
            $aAddress['Postcode'] = '-';
        }
        if (empty($sCity)) {
            $sCity = '-';
        }

        // set the shipping phone number if the phone number is missing from other address types
        if (empty($aAddress['Phone']) && !empty($addressSets['Shipping']['Phone'])) {
            $aAddress['Phone'] = $addressSets['Shipping']['Phone'];
        }

        // Necessary to be null or a string - [/0/defaultBillingAddress/phoneNumber] Dieser Wert sollte vom Typ string sein.
        if ($aAddress['Phone'] == false) {
            $aAddress['Phone'] = null;
        }

        // Check if PackstationCustomerID is same as AddressAddition, so we can leave AddressAddition empty
        if (isset($this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationCustomerID'])
            && $this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationCustomerID'] === $aAddress['AddressAddition']
        ) {
            $aAddress['AddressAddition'] = null;
        }

        // Always generate new UUID for order addresses to prevent foreign key constraint violations
        // when importing multiple orders with the same email address
        $addressId = Uuid::randomHex();

        $address = [
            'id'                     => $addressId,
            'company'                => $aAddress['Company'],
            'firstName'              => (strlen($aAddress['Firstname'].'') > 50) ? $this->cutText('Address.Firstname', $aAddress['Firstname'], 50) : $aAddress['Firstname'],
            'lastName'               => (strlen($aAddress['Lastname'].'') > 60) ? $this->cutText('Address.Lastname', $aAddress['Lastname'], 60) : $aAddress['Lastname'],
            'city'                   => $sCity,
            'street'                 => $aAddress['StreetAddress'],
            'zipcode'                => $aAddress['Postcode'],
            'salutationId'           => $sSalutationId,
            'countryId'              => $sCountryId,
            'phoneNumber'            => $aAddress['Phone'],
            'additionalAddressLine1' => $aAddress['AddressAddition'],
            'additionalAddressLine2' => null,
            'email'                  => $aAddress['EMail'],
            'vatId'                  => $aAddress['UstId'] ?? null,
        ];

        if ($addressType === 'Shipping') {
            $address = array_merge($address,
                array(
                    //In Amazon orders if PackstationID is set then it is filling "order -> street" with PackstationID value else it is filling  with NULL value
                    'street' => (isset($this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationID'])) ? 'Packstation '.$this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationID'] : $aAddress['StreetAddress'],
                    //In Amazon orders if PackstationCustomerID is set then it is filling "order -> additionalAddressLine1" with PackstationCustomerID value else it is filling with NULL value
                    'additionalAddressLine1' => (isset($this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationCustomerID'])) ? $this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationCustomerID'] : $aAddress['AddressAddition'],
                    'additionalAddressLine2' => (isset($this->aNewData['MPSpecific']['DeliveryPackstation']['PackstationCustomerID'])) ? $aAddress['AddressAddition'] : null,
                )
            );
        }

        if ($iStateId !== null) {
            $address['countryStateId'] = Uuid::fromBytesToHex($iStateId);
        }

        return $address;
    }


    /**
     * Check for duplicate address date for a specific customer
     *
     * @param $type
     * @param $address
     * @param $customerId
     * @return bool
     */
    protected function checkForDuplicateAddress($type, $address, $customerId) {
        $oExistingAddresses = MLShopware6Alias::getRepository($type.'_address.repository')
            ->search((new Criteria())
                ->addFilter(new EqualsFilter('customerId', $customerId))
                ->addFilter(new EqualsFilter('firstName', $address['firstName'])),
                Context::createDefaultContext()
            )
            ->getEntities();

        $blNewAddress = true;
        /** @var CustomerAddressEntity|OrderAddressEntity $existingAddress */
        foreach ($oExistingAddresses as $existingAddress) {
            if (
                (
                    $existingAddress->getPhoneNumber() === $address['phoneNumber']
                    || (empty($existingAddress->getPhoneNumber()) && empty($address['phoneNumber'])) // phoneNumber in shop could be empty string or null
                )
                && ($existingAddress->getFirstName() === $address['firstName'])
                && ($existingAddress->getLastName() === $address['lastName'])
                && ($existingAddress->getStreet() === $address['street'])
                && ($existingAddress->getZipcode() === $address['zipcode'])
                && ($existingAddress->getCity() === $address['city'])
            ) {
                $blNewAddress = false;
            }
        }
        return $blNewAddress;
    }

    /**
     * try to find matched shipping method in shopware otherwise it create new shipping method
     * @return string
     */
    protected function getShippingMethod(): string {
        $aTotalShipping = $this->getTotal('Shipping');
        $sShippingMethodId = null;
        if (!empty($aTotalShipping['Code'])) {
            if (Uuid::isValid($aTotalShipping['Code'])) {
                return $aTotalShipping['Code'];
            } else {
                // First try to find by name
                $sShippingMethodId = MLShopware6Alias::getRepository('shipping_method')
                    ->searchIds((new Criteria())
                        ->addFilter(new EqualsFilter('name', $aTotalShipping['Code'])), Context::createDefaultContext())
                    ->firstId();

                // If not found by name, try to find by technical_name (for Shopware 6.5.7+)
                // technicalName field was introduced in 6.5.7.0
                if ($sShippingMethodId === null && version_compare(MLSHOPWAREVERSION, '6.5.7.0', '>=')) {
                    $sShippingMethodId = MLShopware6Alias::getRepository('shipping_method')
                        ->searchIds((new Criteria())
                            ->addFilter(new EqualsFilter('technicalName', $aTotalShipping['Code'])), Context::createDefaultContext())
                        ->firstId();
                }

                if ($sShippingMethodId === null) {//try to create a new shipping method
                    $sShippingMethodId = Uuid::randomHex();
                    $sRuleId = MLShopware6Alias::getRepository('rule')
                        ->searchIds((new Criteria())
                            ->addFilter(new EqualsFilter('invalid', false)), Context::createDefaultContext())->firstId();


                    $sDeliveryTimeId = MLShopware6Alias::getRepository('delivery_time')
                        ->searchIds(new Criteria(), Context::createDefaultContext())->firstId();
                    $aShippingMethodData = [
                        [
                            'id'                 => $sShippingMethodId,
                            'name'               => $aTotalShipping['Code'],
                            'active'             => false,
                            'availabilityRuleId' => $sRuleId,
                            'deliveryTimeId'     => $sDeliveryTimeId,
                            'translations'       => [
                                'de-DE' => [
                                    'name' => $aTotalShipping['Code'],
                                ],
                                'en-GB' => [
                                    'name' => $aTotalShipping['Code'],
                                ],
                            ],
                        ]
                    ];

                    // technicalName field was introduced in Shopware 6.5.7.0 (optional) and became required in 6.7.0.0
                    if (version_compare(MLSHOPWAREVERSION, '6.5.7.0', '>=')) {
                        $aShippingMethodData[0]['technicalName'] = $aTotalShipping['Code'];
                    }

                    MLShopware6Alias::getRepository('shipping_method')->create($aShippingMethodData, Context::createDefaultContext())->getErrors();


                }
            }
            return $sShippingMethodId;
        }
        return $this->getAvailableShippingMethod()->getId();
    }

    /**
     * Get first available shipping method
     * @return ShippingMethodEntity
     * @see \Shopware\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour::getAvailableShippingMethod
     */
    protected function getAvailableShippingMethod(): ShippingMethodEntity {
        /** @var EntityRepositoryInterface $repository */
        $repository = $this->getContainer()->get('shipping_method.repository');

        $shippingMethods = $repository->search(
            (new Criteria())
                ->addAssociation('prices')
                ->addFilter(new EqualsFilter('shipping_method.prices.calculation', 1)),
            Context::createDefaultContext()
        )->getEntities();

        /** @var ShippingMethodEntity $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            if ($shippingMethod->getAvailabilityRuleId() !== null) {
                return $shippingMethod;
            }
        }

        throw new \LogicException('No available ShippingMethod configured');
    }

    protected function getStateId($sState, $sCountryId) {
        $query = $this->getShopwareDb()->createQueryBuilder();
        $query->select([
            'country_state_id',
        ]);
        $query->from('country_state', 'c');
        $query->innerJoin('c', 'country_state_translation', 's', 'c.id = s.country_state_id AND c.country_id = :country_id');
        $query->andWhere('s.name LIKE :state');

        $query->setParameter('country_id', Uuid::fromHexToBytes($sCountryId));
        $query->setParameter('state', $sState);

        $data = $query->execute()->fetchAll();
        if (count($data) > 0) {
            return current($data)['country_state_id'];
        }
        return null;
    }


    /**
     * Get Salutation Id of Shopware 6 for a given gender
     *  Use als Fallback "not_specified" or just the first salutation
     *
     * @param $sGender === 'f' ? 'mrs' : 'mr'
     * @return mixed
     */
    protected function getSalutationId($sGender) {
        // 'mrs' and 'mr' are default values of shopware 6 that can be adjusted
        if ($sGender !== false) {
            $sGender = $sGender === 'f' ? 'mrs' : 'mr';
            $salutationCriteria = new Criteria();
            $salutationId = MagnalisterController::getShopwareMyContainer()
                                ->get('salutation.repository')
                                ->searchIds($salutationCriteria->addFilter(new EqualsFilter('salutationKey', $sGender)), Context::createDefaultContext())
                                ->getIds()[0];

        } else {
            $salutation = MagnalisterController::getShopwareMyContainer()
                                ->get('salutation.repository')
                                ->searchIds((new Criteria())->addFilter(new EqualsFilter('salutationKey', 'not_specified')), Context::createDefaultContext())
                                ->getIds();
            if (is_array($salutation) && count($salutation) > 0) {
                $salutationId = $salutation[0];
            }
        }

        // if "not_specified" is also not found use one of the available salutations
        if (empty($salutationId)) {
            $salutationId = MagnalisterController::getShopwareMyContainer()
                ->get('salutation.repository')
                ->searchIds(new Criteria(), Context::createDefaultContext())
                ->getIds()[0];
        }

        return $salutationId;
    }

    protected function getCountryId($iCountryCode) {

        $sMlOrderId = MLSetting::gi()->get('sCurrentOrderImportMarketplaceOrderId');
        if (!empty($iCountryCode)) {
            $countryCriteria = new Criteria();
            $aCountryId = MagnalisterController::getShopwareMyContainer()
                ->get('country.repository')
                ->searchIds($countryCriteria->addFilter(new EqualsFilter('iso', $iCountryCode)), Context::createDefaultContext())
                ->getIds();
            if (!is_array($aCountryId) || count($aCountryId) === 0) {
                $message = MLI18n::gi()->get('Shopware_Orderimport_CountryCodeDontExistsError', array('mpOrderId' => $sMlOrderId, 'ISO' => $iCountryCode));
                MLErrorLog::gi()->addError(0, ' ', $message, array('MOrderID' => $sMlOrderId));
                throw new Exception($message);
            }
        } else {
            $message = MLI18n::gi()->get('Shopware_Orderimport_CountryCodeIsEmptyError', array('mpOrderId' => $sMlOrderId));
            MLErrorLog::gi()->addError(0, ' ', $message, array('MOrderID' => $sMlOrderId));
            throw new Exception($message);
        }


        return $aCountryId[0];
    }

    /**
     * find or create payment method with payment method code
     * @return string
     * @throws Exception
     */
    protected function getPaymentMethodId() {
        $sPaymentId = null;
        $sPaymentMethod = $this->getTotal('Payment')['Code'];
        if (Uuid::isValid(strtolower($sPaymentMethod))) {
            $sPaymentId = strtolower($sPaymentMethod);
        } else {
            $query = $this->getShopwareDb()->createQueryBuilder();
            $query->select([
                'LOWER(HEX(p.id)) as id',
            ]);
            $query->from('payment_method', 'p');
            $query->innerJoin('p', 'payment_method_translation', 'pt', 'p.id = pt.payment_method_id AND pt.name = :payment_method_name');
            $query->setParameter('payment_method_name', $sPaymentMethod);
            $data = $query->execute()->fetchAll();
            if (count($data) > 0) {
                $sPaymentId = current($data)['id'];
            } else {
                $sPaymentId = Uuid::randomHex();
                $data = [
                    'id'                => $sPaymentId,
                    'handlerIdentifier' => 'Shopware\Core\Checkout\Payment\Cart\PaymentHandler\DefaultPayment',
                    'afterOrderEnabled' => true,
                    'translations'      => [
                        'de-DE' => [
                            'name' => $sPaymentMethod,
                        ],
                        'en-GB' => [
                            'name' => $sPaymentMethod,
                        ],
                    ],
                ];
                $context = Context::createDefaultContext();
                /**
                 * @var $oDefaultLanguage \Shopware\Core\System\Language\LanguageEntity
                 */
                $oDefaultLanguage = MLShopware6Alias::getRepository('language')
                    ->search((new Criteria([Defaults::LANGUAGE_SYSTEM]))->addAssociation('locale'), $context)->first();
                if (isset($oDefaultLanguage)) {
                    $data['translations'][$oDefaultLanguage->getLocale()->getCode()] = [
                        'name' => $sPaymentMethod,
                    ];
                }

                MLShopware6Alias::getRepository('payment_method')->create([$data], $context);

                //Add new payment method to sale channel
                //It seems it is not necessary to add new payment method to the sale channel just keep code for future
                /* $aPaymentMethods = $this->getSalesChannel()->getPaymentMethodIds();
                 $aPaymentMethodData = [];
                 foreach($aPaymentMethods as  $sPaymentMethodId){
                     $aPaymentMethodData[] = ['id'=>$sPaymentMethodId];
                 }
                 $aPaymentMethods[] = ['id'=>$sPaymentId];
                 MLShopware6Alias::getRepository('sales_channel')
                     ->update([
                         [
                             'id'             => $this->getSalesChannel()->getId(),
                             'paymentMethods' => $aPaymentMethodData,
                         ]
                     ], Context::createDefaultContext());*/
            }
        }

        return $sPaymentId;
    }


    /**
     * @return ContainerInterface
     */
    private function getContainer(): ContainerInterface {
        return MagnalisterController::getShopwareMyContainer();
    }


    /**
     * @return Connection
     */
    protected function getShopwareDb() {
        return MagnalisterController::getShopwareConnection();
    }


    /**
     * @return SalesChannelEntity
     * @throws Exception
     */
    public function getSalesChannel(): SalesChannelEntity {
        $oModul = MLModule::gi();
        $iShopId = $oModul->getConfig('orderimport.shop');
        $oSalesChannel = MLShopware6Alias::getRepository('sales_channel.repository')->search(new Criteria([$iShopId]), Context::createDefaultContext())->getEntities()->first();
        if ($oSalesChannel !== null) {
            return $oSalesChannel;
        } else {
            throw new Exception('cannot find configured sales_channel');
        }
    }


    protected function findCustomerByEmail() {
        $oCriteria = new Criteria();
        return MLShopware6Alias::getRepository('customer.repository')->searchIds($oCriteria->addFilter(new EqualsFilter('email', $this->aNewData['AddressSets']['Main']['EMail'])), Context::createDefaultContext())->firstId();
    }

    /**
     * Get the customer default address id based on address type
     *
     * @param string $sCustomerId
     * @param string $addressType 'billing' | 'shipping'
     * @return string|null
     */
    protected function getCustomerDefaultAddressId($sCustomerId, $addressType) {
        $criteria = new Criteria([$sCustomerId]);
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('defaultBillingAddress');
        $criteria->addAssociation('defaultShippingAddress');

        $customer = MLShopware6Alias::getRepository('customer.repository')
            ->search($criteria, Context::createDefaultContext())
            ->first();

        if (!$customer) {
            return null;
        }
        $addressId = null;

        if ($addressType === 'billing') {
            $addressId = $customer->getDefaultBillingAddressId();
        } elseif ($addressType === 'shipping') {
            $addressId = $customer->getDefaultShippingAddressId();
        }

        return $addressId;
    }

    protected function findAddressesByDetails() {
        $oCriteria = new Criteria();
        return MLShopware6Alias::getRepository('customer_address.repository');
    }


    public function getOrderStateId(string $state, string $machine) {
        $sSql = '
                SELECT LOWER(HEX(`state_machine_state`.id))
                FROM `state_machine_state`
                    INNER JOIN  `state_machine`
                    ON `state_machine`.`id` = `state_machine_state`.`state_machine_id`
                    AND `state_machine`.`technical_name` = :machine
                WHERE `state_machine_state`.`technical_name` = :state
            ';
        $aParams = [
            'state'   => $state,
            'machine' => $machine,
        ];
        if (version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')) {
            //fetchColumn method has been deprecated and replaced by  fetchOne method in Doctrine
            return $this->getContainer()->get(Connection::class)
                ->fetchOne($sSql, $aParams);
        } else {
            return $this->getContainer()->get(Connection::class)
                ->fetchColumn($sSql, $aParams);
        }
    }

    protected function getDeliveryPosition($aLineItems): array {
        $aPositions = [];
        foreach ($aLineItems as $item) {
            $aPositions[] = [
                'id'              => Uuid::randomHex(),
                'orderLineItemId' => $item['id'],
                'price'           => $item['price'],
                'quantity'        => $item['quantity'],
            ];
        }
        return $aPositions;
    }

    /**
     * @param float $fProductPrice
     * @param int $iProductQuantity
     * @param float $fProductTaxRate
     * @return [CalculatedPrice, QuantityPriceDefinition]
     * @throws Exception
     */
    protected function getProductPrice(float $fProductPrice, int $iProductQuantity, float $fProductTaxRate): array {
        $fProductTotalPrice = $fProductPrice * $iProductQuantity;
        $fProductTotalPriceNet = MLPrice::factory()->calcPercentages($fProductTotalPrice, null, $fProductTaxRate);
        $this->fMaxProductTax = max($this->fMaxProductTax, $fProductTaxRate);
        $oTaxRule = new TaxRule($fProductTaxRate);
        $oTaxRuleCollection = new TaxRuleCollection([$oTaxRule]);
        $this->oTotalTaxRuleCollection->add($oTaxRule);
        $this->addTotalAmount($fProductTotalPrice, $fProductTotalPriceNet);
        $oCalculatedTax = new CalculatedTax($fProductTotalPrice - $fProductTotalPriceNet, $fProductTaxRate, $fProductTotalPrice);
        $oCalculatedTaxCollection = new CalculatedTaxCollection([$oCalculatedTax]);
        $this->addTotalCalculatedTaxCollection($oCalculatedTax);

        if (version_compare(MLSHOPWAREVERSION, '6.4.0.0', '>=')) {
            $oQuantityPriceDefintion = new QuantityPriceDefinition($fProductPrice, $oTaxRuleCollection, $iProductQuantity);
        } else {
            $oQuantityPriceDefintion = new QuantityPriceDefinition($fProductPrice, $oTaxRuleCollection, 4, $iProductQuantity);
        }

        return [
            new CalculatedPrice(
                $fProductPrice,
                $fProductTotalPrice,
                $oCalculatedTaxCollection,
                $oTaxRuleCollection,
                $iProductQuantity
            ),
            $oQuantityPriceDefintion
        ];
    }


    /**
     * shopware create automatically new order number for new order
     * with this function it is easier to override this number
     * @return string
     * @throws Exception
     */
    protected function getShopwareOrderNumber(): ?string {
        if ($this->oExistingOrder !== null) {
            return $this->oExistingOrder->getOrderNumber();
        } else {
            return MagnalisterController::getNumberRangeValueGenerator()->getValue(
                $this->getContainer()->get('order.repository')->getDefinition()->getEntityName(),
                Context::createDefaultContext(),
                $this->getSalesChannel()->getId()
            );
        }
    }


    /**
     * In new changes of Shopware 6 there is no need to this function. Maybe in future it will be useful.
     * stock reduction will be done depends on state of order
     * @param $sFromStateId
     * @throws Exception
     */
    protected function stockManagement($sFromStateId): void {
        if(version_compare(MLSHOPWAREVERSION, '6.6.0.0', '>=')) {
            throw new Exception('Something with loading correct class is wrong. Overrided function in ShopVersion should be loaded');
        }
        $this->handleStatusEvents($this->aNewData, $this->oOrder->getShopOrderObject()->getStateMachineState()->getTechnicalName());
        $context = $this->getContextWithOrderParameter($this->aNewData);
        $event = new StateMachineTransitionEvent(
            'order',
            $this->oOrder->getShopOrderObject()->getId(),
            MLShopware6Alias::getRepository('state_machine_state')->search(new Criteria([$sFromStateId]), Context::createDefaultContext())->first(),
            $this->oOrder->getShopOrderObject()->getStateMachineState(),
            $context
        );
        MagnalisterController::getStockUpdater()->stateChanged($event);
        //In shopware 6.5 it is necessary to run the "StockUpdater::update" to reduced stock and stock available after importing order with complete status.
        //In shopware 6.4 it is not necessary to run the "StockUpdater::update" to reduced stock and stock available after importing order with complete status.
        MagnalisterController::getStockUpdater()->update(array($this->oOrder->getShopOrderObject()->getId()),Context::createDefaultContext());
    }

    /**
     * @return CalculatedTaxCollection
     */
    protected function getTotalCalculatedTaxCollection(): CalculatedTaxCollection {
        if ($this->oTotalCalculatedTaxCollection === null) {
            $this->oTotalCalculatedTaxCollection = new CalculatedTaxCollection();
        }
        return $this->oTotalCalculatedTaxCollection;
    }

    protected function addTotalCalculatedTaxCollection(CalculatedTax $oCalculatedTax) {
        $oCalculatedTaxCollection = new CalculatedTaxCollection([$oCalculatedTax]);
        $this->oTotalCalculatedTaxCollection = $this->getTotalCalculatedTaxCollection()->merge($oCalculatedTaxCollection);
    }

    /**
     * If no payment status is set it return 'open' as open status
     *
     * @return null|string
     */
    protected function getPaymentStatus() {
        // E.g.: Idealo and Check24 do not set PaymentStatus in Normalize
        if (empty($this->aNewData['Order']['PaymentStatus'])) {// it could be filled in Normalize files (e.g. eBay)
            $this->aNewData['Order']['PaymentStatus'] = MLModule::gi()->getConfig('orderimport.paymentstatus');

            // in some marketplaces its just "paymentstatus"
            if ($this->aNewData['Order']['PaymentStatus'] === null) {
                $this->aNewData['Order']['PaymentStatus'] = MLModule::gi()->getConfig('paymentstatus');
            }

            // Last Fallback
            if (empty($this->aNewData['Order']['PaymentStatus'])) {
                $this->aNewData['Order']['PaymentStatus'] = OrderTransactionStates::STATE_OPEN;
            }
        }
        return $this->aNewData['Order']['PaymentStatus'];

    }

    /**
     * @param array $aData
     * @see \Shopware\Core\Content\Flow\Dispatching\FlowDispatcher::callFlowExecutor
     */
    protected function handleOrderEvents($aData) {
        if(version_compare(MLSHOPWAREVERSION, '6.6.0.0', '>=')) {
            throw new Exception('Something with loading correct class is wrong. Overrided function in ShopVersion should be loaded');
        }
        $oConfig = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.shopware6flowskipped');
        $sEscape = $oConfig->get('value');
        if ($sEscape === '1') {
            return;
        }
        $oConfig->set('value', 1)->save();
        try {
            $context = $this->getContextWithOrderParameter($aData);
            $OrderFixedQuantity = $this->oOrder->getShopOrderObject();
            //in Showpare 6.5, setting the quantity of order with complete status as '0' will avoid available stock reduction
            //in Showpare 6.4 it is ok to set the quantity of order with complete status as '0', and it will reduce stock and available stock
            if (version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')) {
                if ($OrderFixedQuantity->getStateMachineState()->getTechnicalName() !== OrderStates::STATE_COMPLETED) {
                    // by calling CheckoutOrderPlacedEvent event the Shopware run \Shopware\Core\Content\Product\DataAbstractionLayer\StockUpdater::orderPlaced
                    // and this function trys to reduce the quantity of existing product again, but it has benn already reduced in stockManagement function
                    //It is mandatory to add the quantity as 0 to prevent additional reduction in existing product
                    foreach ($OrderFixedQuantity->getLineItems() as $lineItem) {
                        $lineItem->setQuantity(0);
                    }
                }
            }
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
     * @param array $aData
     * @see \Shopware\Core\Content\Flow\Dispatching\FlowDispatcher::callFlowExecutor
     */
    protected function handleStatusEvents($aData, $sStatus = 'open', $sStatusGroup = 'order') {
        $oConfig = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.shopware6flowskipped');
        $sEscape = $oConfig->get('value');
        if ($sEscape === '1') {
            return;
        }
        $oConfig->set('value', 1)->save();
        try {
            $context = $this->getContextWithOrderParameter($aData);
            //Kint::dump('state_enter.'.$sStatusGroup.'.state.'.$sStatus);
            $event = new OrderStateMachineStateChangeEvent('state_enter.'.$sStatusGroup.'.state.'.$sStatus, $this->oOrder->getShopOrderObject(), $context);
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
     * @param $currencyCode
     * @return Context
     * @throws Exception
     */
    protected function getContextWithOrderParameter($aData): Context {
        $currencyCode = $aData['Order']['Currency'];
        $aConfig = MLModule::gi()->getConfig();
        $iLangId = Uuid::isValid($aConfig['lang']) ? $aConfig['lang'] : null;
        $currency = MLShopware6Alias::getRepository('currency')
            ->search((new Criteria())->addFilter(new EqualsFilter('isoCode', (string)$currencyCode)), Context::createDefaultContext())->first();
        $currencyId = $currency === null ? null : $currency->getId();
        $basicContext = MLShopware6Alias::getContext($iLangId, $currencyId);
        /** @var \Shopware\Core\Content\Rule\RuleEntity[] $aRules */
        $aRules = MLShopware6Alias::getRepository('rule')->search(new Criteria(), $basicContext)->getEntities();
        $sConfigCustomerGroup = $this->getCustomerGroup();
        $customerGroupId = $sConfigCustomerGroup === '-' ? $this->getSalesChannel()->getCustomerGroupId() : $sConfigCustomerGroup;
        $oCustomerGroup = MLShopware6Alias::getRepository('customer_group')->search(new Criteria([$customerGroupId]), $basicContext)->first();

        $customer = $this->getCustomer();
        if(version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')){
            $salesChannelContext = new SalesChannelContext(
                $basicContext,
                '',
                null,
                $this->getSalesChannel(),
                $currency,
                $oCustomerGroup,
                new TaxCollection([]),
                MLShopware6Alias::getRepository('payment_method')->search(new Criteria([$this->getPaymentMethodId()]), $basicContext)->first(),
                MLShopware6Alias::getRepository('shipping_method')->search(new Criteria([$this->getShippingMethod()]), $basicContext)->first(),
                new ShippingLocation(
                    $customer->getDefaultShippingAddress()->getCountry(),
                    $customer->getDefaultShippingAddress()->getCountryState(),
                    $customer->getDefaultShippingAddress()
                ),
                $customer,
                new CashRoundingConfig(2, 0.01, true),
                new CashRoundingConfig(2, 0.01, true),
                []
            );
        }
        elseif (version_compare(MLSHOPWAREVERSION, '6.4.0.0', '>=')) {
            $salesChannelContext = new SalesChannelContext(
                $basicContext,
                '',
                null,
                $this->getSalesChannel(),
                $currency,
                $oCustomerGroup,
                $oCustomerGroup,//@deprecated tag:v6.5.0 - Parameter $fallbackCustomerGroup is deprecated and will be removed
                new TaxCollection([]),
                MLShopware6Alias::getRepository('payment_method')->search(new Criteria([$this->getPaymentMethodId()]), $basicContext)->first(),
                MLShopware6Alias::getRepository('shipping_method')->search(new Criteria([$this->getShippingMethod()]), $basicContext)->first(),
                new ShippingLocation(
                    $customer->getDefaultShippingAddress()->getCountry(),
                    $customer->getDefaultShippingAddress()->getCountryState(),
                    $customer->getDefaultShippingAddress()
                ),
                $customer,
                new CashRoundingConfig(2, 0.01, true),
                new CashRoundingConfig(2, 0.01, true),
                []
            );
        } else {//shopware 6.3.x
            $shipping = new CustomerAddressEntity();
            if ($customer->getDefaultShippingAddress()->getCountry() !== null) {
                $shipping->setCountry($customer->getDefaultShippingAddress()->getCountry());
            }
            if ($customer->getDefaultShippingAddress()->getCountryState() !== null) {
                $shipping->setCountryState($customer->getDefaultShippingAddress()->getCountryState());
            }
            $salesChannelContext = new SalesChannelContext(
                $basicContext,
                Uuid::randomHex(),
                $this->getSalesChannel(),
                $currency,
                $oCustomerGroup,
                $oCustomerGroup,
                new TaxCollection([]),
                MLShopware6Alias::getRepository('payment_method')->search(new Criteria([$this->getPaymentMethodId()]), $basicContext)->first(),
                MLShopware6Alias::getRepository('shipping_method')->search(new Criteria([$this->getShippingMethod()]), $basicContext)->first(),
                ShippingLocation::createFromAddress($shipping),
                $customer,
                []
            );
        }

        // Create an empty cart for CartRuleScope (required for Shopware 6.5.8+)
        // Some rules require cart context and call getCart() on the scope
        $cart = new Cart(Uuid::randomHex());
        $scope = new CartRuleScope($cart, $salesChannelContext);
        $aRuleIds = [];
        foreach ($aRules as $rule) {
            try {
                $rootCondition = $rule->getPayload();
                if ($rootCondition !== null && $rootCondition->match($scope)) {
                    $aRuleIds[] = $rule->getId();
                }
            } catch (\Throwable $ex) {
                // Ignore rules that fail to evaluate with empty cart context
                // This can happen with cart-specific rules that require actual cart data
                MLMessage::gi()->addDebug("Rule evaluation skipped for rule ID " . $rule->getId() . ": " . $ex->getMessage());
            }
        }
        return MLShopware6Alias::getContext($iLangId, $currencyId, $aRuleIds);
    }


    protected function cutText($sFieldName, $sValidated, $iLength, $blDottedEnd = true) {
        $this->aCuttedField[$sFieldName] = $sValidated;
        if (function_exists('mb_substr')) {
            if ($blDottedEnd) {
                $sValidated = mb_substr($sValidated, 0, $iLength - 4, 'UTF8')."...";
            } else {
                $sValidated = mb_substr($sValidated, 0, $iLength, 'UTF8');
            }
        } else {
            if ($blDottedEnd) {
                $sValidated = substr($sValidated, 0, $iLength - 4)."...";
            } else {
                $sValidated = substr($sValidated, 0, $iLength);
            }
        }
        return $sValidated;
    }

    /**
     * Execute repository operation with transaction safety
     * @param callable $operation The repository operation to execute
     * @param string $operationName Description of the operation for error messages
     * @return mixed The result of the operation
     * @throws Exception
     */
    protected function executeWithTransaction(callable $operation, string $operationName) {
        $oDb = MagnalisterController::getShopwareConnection();
        $blTransactionStarted = false;
        
        // Start transaction if not already active
        if (!$oDb->isTransactionActive()) {
            try {
                $oDb->beginTransaction();
                $blTransactionStarted = true;
            } catch (\Exception $ex) {
                MLMessage::gi()->addDebug($ex);
                throw new Exception("Failed to start transaction for {$operationName}: " . $ex->getMessage(), 0, $ex);
            }
        }
        
        try {
            $result = $operation();
            
            // Commit only if we started the transaction
            if ($blTransactionStarted && $oDb->isTransactionActive()) {
                $oDb->commit();
            }
            
            return $result;
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
            
            // Rollback only if we started the transaction and it's still active
            if ($blTransactionStarted && $oDb->isTransactionActive()) {
                try {
                    $oDb->rollBack();
                } catch (\Exception $rollbackEx) {
                    MLMessage::gi()->addDebug($rollbackEx);
                }
            }
            
            // Re-throw with more context
            throw new Exception("Failed to execute {$operationName}: " . $ex->getMessage(), 0, $ex);
        }
    }
}
