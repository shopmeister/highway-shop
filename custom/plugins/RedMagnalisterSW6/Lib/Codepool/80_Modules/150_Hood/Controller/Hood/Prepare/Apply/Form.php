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

MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract');

class ML_Hood_Controller_Hood_Prepare_Apply_Form extends ML_Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract {

    protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

    public function __construct() {
        parent::__construct();
        if ($this->oSelectList->getCountTotal() == 1 && $this->oProduct->get('parentid') != 0) {
            $this->oProduct = $this->oProduct->getParent();
            $this->oPrepareHelper->setProduct($this->oProduct);
        }
    }

    protected function getProductId() {

        if (isset($this->oProduct)) {
            $aVariations = $this->oProduct->getVariants();
            if (isset($aVariations)) {
                return $aVariations[0]->get('id');
            }

            return $sProductId = $this->oProduct->get('id');
        }

        return null;
    }

    protected function getCategoryIdentifierValue() {
        $aMatching = $this->getRequestField();
        return isset($aMatching['primarycategory']) ? $aMatching['primarycategory'] : '';
    }
    protected function triggerBeforeFinalizePrepareAction() {
        $oPreparedProduct = current($this->oPrepareList->getList());
        if (is_object($oPreparedProduct)) {

            $variationMatchingIsValid = parent::triggerBeforeFinalizePrepareAction();

            // Do not run eBay validation if AM is not valid
            if (!$variationMatchingIsValid) {
                return false;
            }

            $oService = $this->verifyItemByMarketplace();
            return !$oService->haveError();
        } else {
            MLMessage::gi()->addDebug("One of products is not existed , please try again");
            return false;
        }
    }

    protected function priceContainerField(&$aField) {
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('listingType', 'id'),
            'trigger' => 'change',
            'field' => array(
                'type' => 'hood_pricecontainer',
            )
        );
    }

    protected function listingDurationField(&$aField) {
        $aField['type'] = 'ajax';
        $sListingType = $this->getField('listingType', 'value');
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('listingType', 'id'),
            'trigger' => 'change',
            'field' => array(
                'type' => 'select',
                'values' => MLModule::gi()->getListingDurations($sListingType),
            )
        );

        if (empty($aField['value']) || MLHttp::gi()->isAjax()) {//it is not in prepareData helper class because additems is ajax too and there it will be ever default value
            $aField['value'] = MLModule::gi()->getConfig(strtolower($sListingType) == 'chinese' ? 'chinese.duration' : 'fixed.duration');
        }
        if (isset($aField['ajax']['field']['values'][-1])) {
            $aField['value'] = current($aField['ajax']['field']['values'][-1]);
        }
    }

    protected function startTimeField(&$aField) {
        $aField['type'] = 'optional';
        $aField['optional']['field']['type'] = 'datetimepicker';
    }

    protected function titleField(&$aField) {
//        $aField['default'] = $this->oPrepareHelper->replaceTitle(MLModul::gi()->getConfig('template.name'));
        $aField['type'] = 'string';
        $aField['maxlength'] = 85;
    }

    protected function subtitleField(&$aField) {
        $aField['optional']['field']['type'] = 'string';
    }

    protected function conditionTypeField(&$aField) {
        $aField['type'] = 'select';
        $aField['values'] = MLModule::gi()->getConditionValues();
    }

    protected function privateListingField(&$aField) {
        $aField['type'] = 'bool';
    }

    protected function imagesField(&$aField) {
        $aField['type'] = 'imagemultipleselect';
    }

    protected function categoriesField(&$aField) {
        foreach ($aField['subfields'] as &$aSubField) {
            $aSubField['cattype'] = empty($aSubField['cattype']) ? 'marketplace' : $aSubField['cattype'];

            $aSubField['values'] = array('' => '..') + MLDatabase::factory('hood_prepare')->{'gettop' . $aSubField['realname'] }();
            //adding current cat, if not in top cat
            if (!array_key_exists((string) $aSubField['value'], $aSubField['values'])) {
                $oCat = MLDatabase::factory(self::getMPName() . '_categories' . $aSubField['cattype']);
                $oCat->init(true)->set('categoryid', $aSubField['value'] ? $aSubField['value'] : 0);

                $sCat = '';

                foreach ($oCat->getCategoryPath() as $oParentCat) {

                    $sCat = $oParentCat->get('categoryname') . ' &gt; ' . $sCat;
                }

                $aSubField['values'][$aSubField['value']] = substr($sCat, 0, -6);
            }

        }
    }

    public function callAjaxGetField() {
        try {
            $aOriginalAjaxData = $this->getAjaxData();

            $aAjaxData = $aOriginalAjaxData;
            $unpackedKey = '';
            $packedKey = '';

            if (isset($aAjaxData['method'])) {
                if (isset($aAjaxData['field'])) {
                    $aField = $aAjaxData['field'];
                    $sField = $this->getRequestField($aField['name']);
                } else {
                    $aField = array('name' => $aAjaxData['method']);
                    $sField = null;
                }
                unset($aField['value']); // value will come from do-method (request-isset value)
                if (array_key_exists('subfields', $aField)) {
                    foreach ($aField['subfields'] as &$aSubField) {
                        unset($aSubField['value']); // value will come from do-method (request-isset value)
                    }
                }
                $aField = $this->getField($aField);
                MLMessage::gi()->addDebug('ajax-field: ' . $aField['realname'], $aField);
                if (isset($aField['type'])) {
                    $domId = $aField['id'];
                    if (!empty($packedKey) && !empty($unpackedKey)) {
                        $domId = str_replace(strtolower($packedKey), $unpackedKey, $domId);
                        $aField['doKeyPacking'] = true;
                    }
                    MLSetting::gi()->add('aAjaxPlugin', array('dom' => array('#' . $domId . '_ajax' => $this->includeTypeBuffered($aField))));
                    return parent::finalizeAjax();
                }
            }
        } catch (Exception $oEx) {
            MLMessage::gi()->addError($oEx->getMessage());
        }
    }

    protected function listingTypeField(&$aField) {
        $aField['values'] = MLModule::gi()->getListingTypeValues();
        $aField['type'] = 'select';
    }

    protected function hitCounterField(&$aField) {
        $aField['values'] = MLModule::gi()->getHitcounterValues();
        $aField['type'] = 'select';
    }

    protected function dispatchTimeMaxField(&$aField) {
//        $aField['default'] = $this->oPrepareHelper->getFromConfig($aField['realname']);
        $aField['values'] = MLI18n::gi()->hood_configform_prepare_dispatchtimemax_values;
    }

    protected function paymentMethodsField(&$aField) {
        $aField['type'] = 'multipleSelect';
        $aField['values'] = MLModule::gi()->getPaymentOptions();
    }

    public function shippingLocalContainerField(&$aField) {

    }

    public function shippingInternationalContainerField(&$aField) {

    }

    protected function _shippingField(&$aField) {
        $aField['type'] = 'duplicate';
        $aField['duplicate']['field']['type'] = 'hood_shippingcontainer_shipping';
    }

    protected function shippingLocalField(&$aField) {
        $aField['values'] = MLModule::gi()->getLocalShippingServices();
        $this->_shippingField($aField);
    }

    protected function shippingInternationalField(&$aField) {
        $aField['autooptional'] = false;
        $aField['values'] = array_merge(array('' => MLI18n::gi()->get('sHoodNoInternationalShipping')), MLModule::gi()->getInternationalShippingServices());
        $this->_shippingField($aField);
    }

    protected function noIdentifierFlagField(&$aField) {
        $aField['values'] = MLModule::gi()->getNoIdentifierFlagValue();
        $aField['type'] = 'select';
    }

    protected function fskField(&$aField) {
        $aField['values'] = MLModule::gi()->getFsk();
    }

    protected function uskField(&$aField) {
        $aField['values'] = MLModule::gi()->getUsk();
    }

    protected function _shippingProfileField(&$aField, $iDefault) {
        $aField['type'] = 'optional';
        $aField['optional']['field']['type'] = 'select';
        $aProfiles = array();
        $oI18n = MLI18n::gi();
        $oPrice = MLPrice::factory();
        $sCurrency = MLModule::gi()->getConfig('currency');
        if (isset($aField['i18n'])) {
            foreach (MLModule::gi()->getShippingDiscountProfiles() as $sProfil => $aProfil) {
                $aProfiles[$sProfil] = $oI18n->replace(
                    $aField['i18n']['option'], array(
                        'NAME' => $aProfil['name'],
                        'AMOUNT' => $oPrice->format($aProfil['amount'], $sCurrency)
                    )
                );
            }
        }
        $aField['values'] = $aProfiles;
    }

//    protected function callGetCategoryDetails($sCategoryId) {
//        $secondaryCategory = $this->getRequestField('SecondaryCategory');
//        $requestParams = array(
//            'ACTION' => 'GetCategoryDetails',
//            'DATA' => array('CategoryID' => $sCategoryId)
//        );
//
//        if (!empty($secondaryCategory) && ($secondaryCategory !== 'none') && ($secondaryCategory !== 'new')) {
//            $requestParams['DATA']['SecondaryCategoryID'] = $secondaryCategory;
//        }
//
//        return MagnaConnector::gi()->submitRequest($requestParams);
//    }

    protected function getPreparedData($sIdentifier, $sCustomIdentifier) {
        $sProductId = $this->getProductId();

        $oPrepareTable = MLDatabase::getPrepareTableInstance();
        $sShopVariationField = $oPrepareTable->getShopVariationFieldName();
        $sPrimaryCategory = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());

        $sPrimaryCategoryValue = isset($sPrimaryCategory['[' . $sProductId . ']']) ? $sPrimaryCategory['[' . $sProductId . ']'] : reset($sPrimaryCategory);
        if (!empty($sPrimaryCategory)) {
            if ($sPrimaryCategoryValue === $sIdentifier) {
                $aShopVariation = $this->oPrepareList->get($sShopVariationField);
                $aValue = isset($aShopVariation['[' . $sProductId . ']']) ? $aShopVariation['[' . $sProductId . ']'] : reset($aShopVariation);

            }
        }
        //there is no need to check the category in hood API attribute and all attribute are define as 1 category
        $aValue = $this->getPreparedShopVariationForList($this->oPrepareList);
        if (!isset($aValue)) {
            //if there is no attribute matching data in magnalister_hood_prepare table, attribute matching form in preparation form will show the attribute matching form canfiguration data from magnalister_hood_variantmatching table
            $aValue = $this->getAttributesFromDB($sIdentifier, $sCustomIdentifier);
        }
        return $aValue;
    }

    protected function getAttributeValues($sIdentifier, $sCustomIdentifier, $sAttributeCode = null, $bFreeText = false)
    {MLMessage::gi()->addDebug('$sIdentifierPlus',$sIdentifier);
        $sIdentifier = '1';
        $aValue = $this->getPreparedData($sIdentifier, $sCustomIdentifier);
        MLMessage::gi()->addDebug('$aValuePlus',$aValue);
        if (is_array($aValue)) {
            if ($sAttributeCode !== null) {
                foreach ($aValue as $sKey => $aMatch) {
                    if ($sKey === $sAttributeCode) {
                        return isset($aMatch['Values']) ? $aMatch['Values'] : ($bFreeText ? '' : array());
                    }
                }
            } else {
                return $aValue;
            }
        }

        if ($bFreeText) {
            return '';
        }

        return array();
    }

    public function triggerAfterField(&$aField, $parentCall = false)
    {
        //TODO Check this parent call
        parent::triggerAfterField($aField);

        if ($parentCall) {
            return;
        }

        $sName = $aField['realname'];

        // when top variation groups drop down is changed, its value is updated in getRequestValue
        // otherwise, it should remain empty.
        // without second condition this function will be executed recursevly because of the second line below.
        if (!isset($aField['value'])) {
            $sProductId = $this->getProductId();

            $oPrepareTable = MLDatabase::getPrepareTableInstance();

            $aPrimaryCategories = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());
            $sPrimaryCategoriesValue = isset($aPrimaryCategories['[' . $sProductId . ']'])
                ? $aPrimaryCategories['[' . $sProductId . ']'] : reset($aPrimaryCategories);

            if ($sName === 'variationgroups.value') {
                $aField['value'] = $sPrimaryCategoriesValue;
            } else {
                // check whether we're getting value for standard group or for custom variation mathing group
                $sCustomGroupName = $this->getField('variationgroups.value', 'value');
                $aCustomIdentifier = explode(':', $sCustomGroupName);

                if (count($aCustomIdentifier) == 2 && ($sName === 'attributename' || $sName === 'customidentifier')) {
                    $aField['value'] = $aCustomIdentifier[$sName === 'attributename' ? 0 : 1];
                    return;
                }

                $aNames = explode('.', $sName);
                if (count($aNames) == 4 && strtolower($aNames[3]) === 'code') {
                    $aValue = $this->getPreparedShopVariationForList($this->oPrepareList);
                    if (is_array($aValue)) {
                        foreach ($aValue as $sKey => &$aMatch) {
                            if (strtolower($sKey) === $aNames[2]) {
                                if (!isset($aMatch['Code'])) {
                                    // this will happen only if attribute was matched and then deleted from the shop
                                    $aMatch['Code'] = '';
                                }
                                $aField['value'] = $aMatch['Code'];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    protected function getMatchingFallback($variationGroups) {
        if (!empty($variationGroups)) {
            return $variationGroups['1'];
        }
    }

    protected function getAttributeIdentifier($categoryId) {
        return '1';
    }
}
