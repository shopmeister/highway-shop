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

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryEntity;

MLFilesystem::gi()->loadClass('Shop_Model_Order_Abstract');

class ML_Shopware6_Model_Order extends ML_Shop_Model_Order_Abstract {

    protected $blUpdatablePaymentStatus;
    protected $blUpdatableOrderStatus;


    public function getUpdatablePaymentStatus() {
        return $this->blUpdatablePaymentStatus;
    }

    public function setUpdatablePaymentStatus($blUpdatable) {
        $this->blUpdatablePaymentStatus = $blUpdatable;
    }

    public function getUpdatableOrderStatus() {
        return $this->blUpdatableOrderStatus;
    }

    public function setUpdatableOrderStatus($blUpdatable) {
        $this->blUpdatableOrderStatus = $blUpdatable;
    }

    /**
     *
     * @param string[] $aAssociations
     * @return OrderEntity
     * @throws Exception
     */
    public function getShopOrderObject(
        array $aAssociations = [
            'deliveries.shippingMethod',
            'deliveries.shippingOrderAddress.country',
            'transactions.paymentMethod',
            'lineItems',
            'currency',
            'addresses.country',
            'stateMachineState',
            'orderCustomer.customer',
        ]
    ): ?OrderEntity {
        if (Uuid::isValid($this->get('current_orders_id'))) {
            $criteria = new Criteria([$this->get('current_orders_id')]);
            $criteria->addAssociations($aAssociations);
            $oOrder = MLShopware6Alias::getRepository('order')->search($criteria, Context::createDefaultContext())->getEntities()->last();
            if (is_object($oOrder)) {
                return $oOrder;
            }
        }
        throw new Exception('This order cannot be found in shop: '.$this->get('current_orders_id'), 1622809739);

    }

    public function existsInShop() {
        try {
            $this->getShopOrderObject();
        } catch (\Exception $ex) {
            if ($ex->getCode() === 1622809739) {
                return false;
            } else {
                throw $ex;
            }
        }
        return true;
    }

    /**
     * return uuid of current order state
     * @return string|null
     */
    public function getShopOrderStatus(): ?string {
        try {
            return $this->getShopOrderObject()->getStateMachineState()->getTechnicalName();
        } catch (Exception $oExc) {
            return null;
        }
    }


    public function getShopPaymentStatus() {
        try {
            if ($this->getShopOrderObject()->getTransactions()->first() !== null) {
                return $this->getShopOrderObject()->getTransactions()->first()->getStateMachineState()->getTechnicalName();
            }
        } catch (Exception $oExc) {
            MLMessage::gi()->addDebug($oExc);
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getShopOrderStatusName() {
        try {
            return $this->getShopOrderObject()->getStateMachineState()->getName();
        } catch (Exception $oExc) {
            return null;
        }
    }

    public function getEditLink() {
        return MLShopware6Alias::getHttpModel()->getAdminUrl().'#/sw/order/detail/'.$this->get('current_orders_id');
    }

    public function getShippingCarrier() {
        $sDefaultCarrier = $this->getMarketplaceDefaultCarrier();
        if ($sDefaultCarrier == '-1') {
            return $this->getShopOrderCarrierOrShippingMethod(); // TODO: Change the autogenerated stub
        } elseif ($sDefaultCarrier == '') {
            return null;
        } else {
            return $sDefaultCarrier;
        }
    }

    public function getShopOrderCarrierOrShippingMethod() {
        try {
            $oOrder = $this->getShopOrderObject();
            /** @var  $oDelivery OrderDeliveryEntity */
            $oDelivery = $oOrder->getDeliveries()->first();

            $sCarrier = $oDelivery->getShippingMethod()->getName();
            return empty($sCarrier) ? null : $sCarrier;
        } catch (Exception $oEx) {
            return null;
        }

    }

    public function getShopOrderCarrierOrShippingMethodId() {
        try {
            $oOrder = $this->getShopOrderObject();
            /** @var  $oDelivery OrderDeliveryEntity */
            $oDelivery = $oOrder->getDeliveries()->first();

            $sCarrier = $oDelivery->getShippingMethod()->getId();
            return empty($sCarrier) ? null : $sCarrier;
        } catch (Exception $oEx) {
            return null;
        }
    }

    public function getShippingDateTime() {
        $mTime = null;
        try {
            $oOrder = $this->getShopOrderObject([
            ]);
            if (MLService::getSyncOrderStatusInstance()->isShipped($oOrder->getStateMachineState()->getTechnicalName())) {
                /** @var StateMachineHistoryEntity $oHistory */
                $sStateID = $oOrder->getStateMachineState()->getId();
                $oHistory = MLShopware6Alias::getRepository('state_machine_history')
                    ->search(
                        (new Criteria())
                            ->addFilter(new EqualsFilter('entityName', OrderDefinition::ENTITY_NAME))
                            ->addFilter(new ContainsFilter('entityId', '"'.$oOrder->getId().'"'))
                            ->addFilter(new EqualsFilter('toStateMachineState.id', $sStateID))
                            ->addSorting(new FieldSorting('createdAt', 'DESC'))
                        , Context::createDefaultContext())->first();
                $mTime = $oHistory === null ? null : $oHistory->getCreatedAt()->format('Y-m-d H:i:s');
            }
        } catch (Exception $oEx) {
            MLMessage::gi()->addDebug($oEx);
        }
        return $mTime;
    }

    public function getShopOrderId() {
        try {
            return $this->getShopOrderObject()->getOrderNumber();
        } catch (Exception $oEx) {//if order deosn't exist in shopware
            return $this->get('orders_id');
        }

    }

    public function setSpecificAcknowledgeField(&$aOrderParameters, $aOrder) {

    }

    public function getShippingDate() {
        return substr($this->getShippingDateTime(), 0, 10);
    }

    public function getShippingTrackingCode() {
        $sTracking = null;
        try {
            $oDelivery = $this->getShopOrderObject()->getDeliveries()->first();
            if ($oDelivery !== null && count($oDelivery->getTrackingCodes()) > 0) {
                $sTracking = $oDelivery->getTrackingCodes()[0];
            }
        } catch (Exception $exc) {
            MLMessage::gi()->addDebug($exc);
        }
        return $sTracking;
    }

    public function getShopOrderLastChangedDate() {
        try {
            if ($this->getShopOrderObject()->getUpdatedAt() !== null) {
                return $this->getShopOrderObject()->getUpdatedAt()->format('Y-m-d H:i:s');
            } else {
                return $this->getShopOrderObject()->getCreatedAt()->format('Y-m-d H:i:s');
            }
        } catch (Exception $oEx) {//if order deosn't exist in shopware
            MLMessage::gi()->addDebug($oEx);
        }
        return null;
    }

    public static function getOutOfSyncOrdersArray($iOffset = 0, $blCount = false) {
        $oQueryBuilder = MLDatabase::factorySelectClass()->select('HEX(so.id) as id')
            ->from(MLShopware6Alias::getRepository('order')->getDefinition()->getEntityName(), 'so')
            ->join(array(MLShopware6Alias::getRepository('state_machine_state')->getDefinition()->getEntityName(), 'sms', 'so.state_id = sms.id'), ML_Database_Model_Query_Select::JOIN_TYPE_INNER)
            ->join(array('magnalister_orders', 'mo', 'HEX(so.id) = mo.current_orders_id'), ML_Database_Model_Query_Select::JOIN_TYPE_INNER)
            ->where("sms.technical_name != mo.status AND mo.mpID='" . MLModule::gi()->getMarketPlaceId() . "' AND so.`version_id` =  UNHEX('" . Context::createDefaultContext()->getVersionId() . "')");

        if ($blCount) {
            return $oQueryBuilder->getCount();
        } else {
            $aOrders = $oQueryBuilder->limit($iOffset, 50)
                ->getResult();

            if (!is_array($aOrders)) {
                MLHelper::gi('stream')->outWithNewLine(MLDatabase::getDbInstance()->getLastError());
            }

            $aOut = array();
            foreach ($aOrders as $aOrder) {
                $aOut[] = $aOrder['id'];
            }
            return $aOut;
        }
    }

    /**
     * @param array $aData
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function shopOrderByMagnaOrderData($aData) {
        /*$blTransactionInMagnalisterInitiated = false;
        $oDb = MagnalisterController::getShopwareConnection();
        try {
            if (!$oDb->isTransactionActive()) {
                $blTransactionInMagnalisterInitiated = true;
                $oDb->beginTransaction();
            }
        } catch (\Exception $oEx) {
            MLMessage::gi()->addDebug($oEx);
        }*/

        $oSHopOrderHelper = MLShopware6Alias::getShopOrderHelper();
        /*try {*/
        $mReturn = $oSHopOrderHelper
            ->setOrder($this)
            ->setNewOrderData($aData)
            ->shopOrder();
        /* if ($blTransactionInMagnalisterInitiated) {
             $oDb->commit();
         }*/
        return $mReturn;
        /* } catch (Exception $oEx) {
             MLMessage::gi()->addDebug($oEx);
             if ($blTransactionInMagnalisterInitiated) {
                 $oDb->rollBack();
             }
             throw $oEx;
         }*/
    }

    public function getShopOrderTotalAmount() {
        try {
            return $this->getShopOrderObject()->getPrice()->getTotalPrice();
        } catch (Exception $exc) {
            MLMessage::gi()->addDebug($exc);
        }
    }

    public function getShopOrderTotalTax() {
        try {
            $oOrderPrice = $this->getShopOrderObject()->getPrice();
            return $oOrderPrice->getTotalPrice() - $oOrderPrice->getNetPrice();
        } catch (Exception $exc) {
            MLMessage::gi()->addDebug($exc);
        }
    }

    /**
     * Check if document is specific type
     *  See Database: document_type | document_type_translation
     *
     * @param $sType
     * @return string
     */
    public function getShopOrderInvoice($sType) {
        $sFileContent = '';
        try {
            //use configuration value from expert settings if provided - fallback is "invoice"
            $configInvoiceType = MLModule::gi()->getConfig('invoice.invoice_documenttype');
            if ($configInvoiceType === null) {
                $configInvoiceType = 'invoice';
            }
            //use configuration value from expert settings if provided - fallback is "cancellation"
            $configCreditNoteType = MLModule::gi()->getConfig('invoice.creditnote_documenttype');
            if ($configCreditNoteType === null) {
                $configCreditNoteType = 'storno';
            }
            foreach ($this->getShopOrderObject(['documents.documentMediaFile', 'documents.documentType'])->getDocuments()->getElements() as $oDocument) {
                if ($this->isInvoiceDocumentType($sType) && $oDocument->getDocumentType()->getTechnicalName() === $configInvoiceType) {
                    if(version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')){
                        //\Shopware\Core\Checkout\Document\Renderer\RenderedDocument
                        //\Shopware\Core\Checkout\Document\Controller\DocumentController::downloadDocument

                        $shopwareDocument = MagnalisterController::getDocumentService()->readDocument($oDocument->getId(), Context::createDefaultContext());
                        if (null == $shopwareDocument) {
                            throw new Exception('The invoice document could not be received from Shopware, the file could be corrupted.');
                        }
                        $sFileContent = base64_encode($shopwareDocument->getContent());
                        //file_put_contents(__DIR__.'/invoice-'.time().'.pdf', MagnalisterController::getDocumentService()->readDocument($oDocument->getId(), Context::createDefaultContext())->getContent());
                    }else{
                        //\Shopware\Core\Checkout\Document\GeneratedDocument
                        //\Shopware\Core\Checkout\Document\Controller\DocumentController::downloadDocument
                        $sFileContent = base64_encode(MagnalisterController::getDocumentService()->getDocument($oDocument, Context::createDefaultContext())->getFileBlob());
                    }
                    //file_put_contents(__DIR__.'/invoice-'.time().'.pdf', MagnalisterController::getDocumentService()->getDocument($oDocument, Context::createDefaultContext())->getFileBlob());
                    //echo print_m('/var/www/php81/swdev65rc3.test/development/custom/plugins/RedMagnalisterSW6/src/Controller');
                    //file_put_contents('/var/www/php81/swdev65rc3.test/development/custom/plugins/RedMagnalisterSW6/src/Controller'.'/invoice-'.time().'.pdf', MagnalisterController::getDocumentService()->readDocument($oDocument->getId(), Context::createDefaultContext())->getContent());
                    break;
                } elseif ($this->isCreditNoteDocumentType($sType) && $oDocument->getDocumentType()->getTechnicalName() === $configCreditNoteType) {
                    if(version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')){
                        //New:\Shopware\Core\Checkout\Document\Renderer\RenderedDocument
                        //\Shopware\Core\Checkout\Document\Controller\DocumentController::downloadDocument

                        $shopwareDocument = MagnalisterController::getDocumentService()->readDocument($oDocument->getId(), Context::createDefaultContext());
                        if (null == $shopwareDocument) {
                            throw new Exception('The credit note document could not be received from Shopware, the file could be corrupted.');
                        }
                        $sFileContent = base64_encode($shopwareDocument->getContent());
                    }else{
                        //\Shopware\Core\Checkout\Document\GeneratedDocument
                        //\Shopware\Core\Checkout\Document\Controller\DocumentController::downloadDocument
                        $sFileContent = base64_encode(MagnalisterController::getDocumentService()->getDocument($oDocument, Context::createDefaultContext())->getFileBlob());
                    }
                    //file_put_contents(__DIR__.'/cancelling_invoice-'.time().'.pdf', MagnalisterController::getDocumentService()->getDocument($oDocument, Context::createDefaultContext())->getFileBlob());
                    break;
                }
            }
        } catch (Exception $exc) {
            MLMessage::gi()->addDebug($exc);
        }
        //if ($sFileContent !== '') {
        //    file_put_contents(__DIR__.'/'.$sType.'.pdf', $sFileContent);
        //}
        return $sFileContent;
    }

    public function getShopOrderInvoiceNumber($sType) {
        $sInvoiceNumber = '';
        try {
            $configInvoiceType = MLModule::gi()->getConfig('invoice.invoice_documenttype');
            if ($configInvoiceType === null) {
                $configInvoiceType = 'invoice';
            }
            //use configuration value from expert settings if provided - fallback is "cancellation"
            $configCreditNoteType = MLModule::gi()->getConfig('invoice.creditnote_documenttype');
            if ($configCreditNoteType === null) {
                $configCreditNoteType = 'storno';
            }
            foreach ($this->getShopOrderObject(['documents', 'documents.documentType'])->getDocuments()->getElements() as $oDocument) {
                if ($this->isInvoiceDocumentType($sType) && $oDocument->getDocumentType()->getTechnicalName() === $configInvoiceType) {
                    $sInvoiceNumber = $oDocument->getConfig()['documentNumber'];
                    break;
                } elseif ($this->isCreditNoteDocumentType($sType) && $oDocument->getDocumentType()->getTechnicalName() === $configCreditNoteType) {
                    $sInvoiceNumber = $oDocument->getConfig()['documentNumber'];
                    break;
                }
            }
        } catch (Exception $exc) {
            MLMessage::gi()->addDebug($exc);
        }
        return $sInvoiceNumber;
    }

    public function getAttributeValue($sName) {
        $sReturn = null;
        if (strpos($sName, 'a_') === 0) {
            $sName = substr($sName, 2);
            $oOrder = $this->getShopOrderObject(['customFields']);
            $oCustomFiledCriteria = (new Criteria())->addFilter(new EqualsFilter('name', $sName));
            $customField = MLShopware6Alias::getRepository('custom_field')->search($oCustomFiledCriteria, Context::createDefaultContext())->getEntities()->first();
            if (is_object($customField) && $customField->getType() === 'select' && isset($oOrder->getCustomFields()[$sName])) {
                    $aConfig = MLModule::gi()->getConfig();
                    if (isset($aConfig['lang']) && $aConfig['lang'] != NULL) {
                        $sLangId = $aConfig['lang'];
                    } else {
                        $sLangId = Defaults::LANGUAGE_SYSTEM;
                    }
                    $Language = MLShopware6Alias::getRepository('language.repository')->search((new Criteria())->addFilter(new EqualsFilter('id', $sLangId)), Context::createDefaultContext())->first();
                    $locale = MLShopware6Alias::getRepository('locale.repository')->search((new Criteria())->addFilter(new EqualsFilter('locale.id', $Language->getLocaleId())), Context::createDefaultContext())->first();
                    $LangCode = $locale->getCode();

                    $sTechnicalName = $oOrder->getCustomFields()[$sName];
                    $customFiledOptions = $customField->getConfig()['options'];
                    foreach ($customFiledOptions as $customFiledOption) {
                        if ($customFiledOption['value'] === $sTechnicalName) {
                            if (isset($customFiledOption['label'][$LangCode])) {
                                $sReturn = $customFiledOption['label'][$LangCode];
                            } else {
                                $sReturn = reset($customFiledOption['label']);
                            }
                        }
                    }
            } else if (isset($oOrder->getCustomFields()[$sName])) {
                $sReturn = $oOrder->getCustomFields()[$sName];
            }
        }
        return $sReturn;
    }

    public function getShopOrderProducts() {
        return array();
    }
}
