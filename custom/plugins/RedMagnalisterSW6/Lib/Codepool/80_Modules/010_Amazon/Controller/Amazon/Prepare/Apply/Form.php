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

class ML_Amazon_Controller_Amazon_Prepare_Apply_Form extends ML_Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract {

    protected $aParameters = array('controller');
    
    /**
     * @var array Store verification errors to share between functions
     */
    protected $verificationErrors = array();

    protected function getSelectionNameValue() {
        return 'apply';
    }

    public function __construct() {
        parent::__construct();

        // Load React bundle and styles
        MLSettingRegistry::gi()->addJs(array(
                'react/dist/magnalister-amazon-variations-bundle.umd.js')
        );
        MLSettingRegistry::gi()->addCss(array(
                'react/dist/style.css'));

        // Load Amazon-specific override for form submission
        // This integrates React save logic before form submission
        MLSettingRegistry::gi()->addJs(array(
                'magnalister.prepareform.recursive.ajax.js'
            )
        );
    }


    /**
     * Amazon override: Skip attribute matching loop (React handles it)
     *
     * This override replaces the parent's full triggerBeforeFinalizePrepareAction method.
     * Instead of processing the entire attribute matching loop (lines 972-1111 in base class),
     * we only execute the general logic methods and then perform Amazon-specific verification.
     *
     * Attribute matching is now handled entirely by the React component (AmazonVariations.tsx)
     * which saves data via AJAX (reactActionSaveAttributeMatching in base class).
     */
    protected function triggerBeforeFinalizePrepareAction() {
        $aActions = $this->getRequest($this->sActionPrefix);
        $savePrepare = $aActions['prepareaction'] === '1';
        $this->oPrepareList->set('preparetype', $this->getSelectionNameValue());
        $this->setPreparedStatus(true);

        // Step 1: Check for prepare errors (general logic)
        if ($this->processPrepareErrorsCheck($savePrepare)) {
            return false;
        }

        $aMatching = $this->getRequestField();

        // Step 2: Process variation theme data (general logic)
        $variationThemeData = $this->processVariationThemeData($aMatching);
        $submittedVariationThemeCode = $variationThemeData['submittedVariationThemeCode'];

        // Step 3: Validate category identifier (general logic)
        $sIdentifier = $this->validateAndGetCategoryIdentifier($aMatching);
        if ($sIdentifier === false) {
            return false;
        }

        $sCustomIdentifier = $this->getCustomIdentifier();

        // Amazon-specific: React component handles all attribute matching
        // We skip the entire attribute matching loop from base class (lines 972-1111)
        // and instead only process general validations

        $aErrors = array();

        // Step 4: Process general validations (general logic)
        // Note: Since React handles attribute matching, we don't count matched attributes here
        // Just pass 0 for numberOfMatchedAdditionalAttributes to skip that validation
        $this->processGeneralValidations($savePrepare, 0, // numberOfMatchedAdditionalAttributes - not applicable for React-based matching
            $this->getNumberOfMaxAdditionalAttributes(), $submittedVariationThemeCode, $aErrors);

        // Step 5: Finalize preparation (general logic)
        // Note: We pass null for $oVariantMatching to skip saveToAttributesMatchingTable
        // because React already saved to amazon_prepare table via AJAX
        $blReturn = $this->finalizePreparation($savePrepare, $aErrors, null, // Don't save to AttributesMatchingTable - React handles saves
            $sIdentifier, $sCustomIdentifier, $aMatching);

        // Amazon-specific verification
        $category = $this->getField('variationgroups.value', 'value');
        if($category !== 'none'){
            $this->oPrepareList->save();
            $oService = $this->verifyItemByMarketplace();
            $hasVerificationError = $oService->haveError();
            // Note: parent::verifyItemByMarketplace() already sets verified status correctly
            $blReturn = $blReturn && !$hasVerificationError;
            if($blReturn && $savePrepare){
                $message = MLI18n::gi()->{'prepareSavedSuccess'};
                // Generate Upload (Checkin) page URL
                $uploadUrl = $this->getCurrentUrl();
                $uploadUrl = str_replace('_prepare_apply_form', '_checkin', $uploadUrl);
                // Replace link placeholder with actual URL
                $messageWithLink = str_replace('{#link#}', $uploadUrl, $message);
                MLMessage::gi()->addSuccess($messageWithLink, '', false);
            }
        } else {
            $this->setPreparedStatus(false);
        }
        return $blReturn;
    }

    /**
     * ShopVariation in new approach will be saved via ajax and react.
     * We DON'T need to load/set it here because Prepare.php save() already handles TextId preservation!
     *
     * See Prepare.php lines 438-469:
     * - If aLoadedTextIds cache is empty AND primary keys are set
     * - Fetch existing TextId from database BEFORE processing longtext fields
     * - This preserves TextId even when form doesn't send shopvariation
     *
     * @param array $shopVariation Parameter from request (empty since React already saved)
     * @param string $category
     */
    protected function saveShopVariationAndPrimaryCategory($shopVariation, $category) {
        $oPrepareTable = MLDatabase::getPrepareTableInstance();

        // Just set primary category - Prepare.php save() handles ShopVariationId preservation
        $this->oPrepareList->set($oPrepareTable->getPrimaryCategoryFieldName(), $category);
    }
    protected function getIdentifier($aMatching)
    {
        $identifier = parent::getIdentifier($aMatching);
        if ($identifier === 'none') {
            return '';
        }
        return $identifier;
    }
    protected function verifyItemByMarketplace() {
        $oService = parent::verifyItemByMarketplace();
        $verifyResponse = $oService->getResponse();
        //the error could be empty in second page of processing products
        if (isset($verifyResponse['ERRORS'])) {
            $this->verificationErrors = $verifyResponse['ERRORS'];
        } else {
            $this->verificationErrors = array();
        }
        return $oService;
    }

    protected function verifyItemByMarketplaceToGetMandatoryAttributes() {
        $category = $this->getField('variationgroups.value', 'value');
        if (empty($category) || $category === 'none') {
            return;
        }
        list($oProduct, $oProductList) = $this->getFirstSelectedProduct();

        /* @var $oService ML_Amazon_Model_Service_AddItems */
        $oService = MLService::getAddItemsInstance()->setValidationMode(true)->setProductList($oProductList);
        $oService->setMandatoryAttributeMode(true);
        try {
            $oService->execute();
        } catch (Exception $oEx) {
            MLMessage::gi()->addDebug($oEx);
        }

        $verifyResponse = $oService->getResponse();
        if ($oService->haveError()) {
            $this->verificationErrors = $verifyResponse['ERRORS'];
        } else {
            $this->verificationErrors = array();
        }
    }
    protected function setPreparedStatus($verified, $productIDs = array()) {
        $this->oPrepareList->set('iscomplete', $verified ? 'true' : 'false');
    }

    protected function variationGroups_valueField(&$aField) {
        $aField['type'] = 'select';
        $aField['select2'] = true;

        $aTopTen = $this->getTopTenCategories('topMainCategory', $aField['name']);
        if (count($aTopTen) > 0) {
            $values = array($this->__('ML_TOPTEN_TEXT') => $aTopTen) + array($this->__('ML_LABEL_CATEGORY') => MLModule::gi()->getMainCategories());
        } else {
            $values = MLModule::gi()->getMainCategories();
        }

        $aField['values'] = array_merge(
            array('none' => MLI18n::gi()->get('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT')),
            $values
        );
    }

    protected function prepareTypeField(&$aField) {
        $aField['value'] = 'apply';
        $aField['type'] = 'hidden';
    }

    protected function variationThemeAllDataField(&$aField) {
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
            'field' => array (
                'type' => 'hidden',
                'value' => '',
            ),
        );

        $categoryId = $this->getField('variationgroups.value', 'value');
        if ($categoryId != '') {
            $categoryDetails = $this->callGetCategoryDetails($categoryId);

            if (!empty($categoryDetails['DATA']['variation_details'])) {
                $aField['ajax']['field']['value'] = htmlspecialchars(json_encode($categoryDetails['DATA']['variation_details']));
                $aField['value'] = htmlspecialchars(json_encode($categoryDetails['DATA']['variation_details']));
            }
        }
    }
    protected function variationMatchingField(&$aField) {
        parent::variationMatchingField($aField);
        if(isset($aField['ajax']['selector'])){
            $aField['ajax']['selector'] .= ', #' . $this->getField('variationthemecode', 'id');
        }
    }

    protected function getVariationThemeAttributes($aCategoryDetails, $categoryId) {
        $aVariationThemeAttributeKeys = array();
        $sVariationThemeValue = $this->getRequestField('variationthemecode');
        if (empty($sVariationThemeValue)) {
            $sVariationThemeValue = $this->getField('variationthemecode', 'value');
        }
        if (!empty($sVariationThemeValue)) {
            $aVariationThemeAttributes = isset($sVariationThemeValue[$categoryId]) && isset($aCategoryDetails['DATA']['variation_details'][$sVariationThemeValue[$categoryId]]['attributes']) ? $aCategoryDetails['DATA']['variation_details'][$sVariationThemeValue[$categoryId]]['attributes'] : array();
            foreach ($aVariationThemeAttributes as $aVariationThemeAttributeKey) {
                $aVariationThemeAttributeKeys[] = $aVariationThemeAttributeKey;
            }
        }

        return $aVariationThemeAttributeKeys;
    }



    protected function variationThemeCodeField(&$aField) {
        //MLSettingRegistry::gi()->addJs('magnalister.attributematching.save.js');
        // Helper for php8 compatibility - overriding deprecated function htmlspecialchars_decode 
        $sField = MLHelper::gi('php8compatibility')->htmlspecialcharsDecode($this->getField('variationthemealldata', 'value'));
        $variationThemes = json_decode($sField, true);
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
        );

        $mParentValue = $this->getField('variationgroups.value', 'value');

        if (is_array($variationThemes) && count($variationThemes) > 0 && $mParentValue != '') {
            $variationThemeNames = array();
            foreach ($variationThemes as $variationThemeKey => $variationTheme) {
                $variationThemeNames[$variationThemeKey] = $variationTheme['name'];
            }

            $aField['values'] = array('null' => $this->__('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT')) + $variationThemeNames;
            $primaryCategory = $this->oPrepareList->get(MLDatabase::getPrepareTableInstance()->getPrimaryCategoryFieldName());
            $differentCategory = $mParentValue !== array_pop($primaryCategory);
            $savedVariationThemes = $differentCategory ? array() : $this->oPrepareList->get('variation_theme');

            $savedVariationTheme = array_pop($savedVariationThemes);
            if(is_array($savedVariationTheme)) {
                $savedVariationThemeCode = key($savedVariationTheme);
                // Value of an ajax field in V3 an array. That array has format :
                // $aField['value'] = array($codeOfDependingField => $variationThemeCode);
                $aField['value'] = array($mParentValue => $savedVariationThemeCode);
            }
            $aField['ajax']['field']['type'] = 'dependonfield';
            $aField['dependonfield']['depend'] = 'variationgroups.value';
            $aField['dependonfield']['field']['type'] = 'select';
            
            // Add error styling if variation theme validation failed
            if ($this->hasVariationThemeError()) {
                $aField['ajax']['field']['cssclasses'] = array('ml-error');
                $aField['labelErrorClass'] = 'ml-error-label';
                //MLMessage::gi()->addDebug('Adding ml-error CSS class to Amazon variation theme field', [$aField['ajax']['field']]);
            } else {
                //MLMessage::gi()->addDebug('NO variation theme error detected in Amazon field rendering');
            }
            $aField['dependonfield']['field']['select2'] = true;
        }
    }

    protected function browseNodesField(&$aField) {
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#'.$this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
        );

        $mParentValue = $this->getField('variationgroups.value', 'value');
        if ($mParentValue != '' && $mParentValue !== 'none') {
            $aBrowseNodes = MLModule::gi()->getBrowseNodes($mParentValue);
            if (!empty($aBrowseNodes)) {
                $aTopTen = $this->getTopTenCategories('topBrowseNode', array($mParentValue));
                if (count($aTopTen) > 0) {
                    // Filter out values from $aBrowseNodes that are already in $aTopTen
                    $aBrowseNodes = array_diff_key($aBrowseNodes, $aTopTen);
                    $aBrowseNodes = array($this->__('ML_QUICK_SELECT') => $aTopTen) + array($this->__('ML_AMAZON_LABEL_APPLY_BROWSENODES') => $aBrowseNodes);
                }

                $aField['values'] = $aBrowseNodes;
                $aField['ajax']['field']['type'] = 'dependonfield';
                $aField['dependonfield']['field']['type'] = 'amazon_browsenodes';
            }
        }
    }

    protected function ItemTitleField(&$aField){
        $aField['type']='string';
        $aField['singleproduct'] = true;
    }

    protected function ManufacturerField(&$aField){
        $aField['type']='string';
        $aField['singleproduct'] = true;
    }

    protected function BrandField(&$aField){
        $aField['type']='string';
        $aField['singleproduct'] = true;
    }

    protected function ManufacturerPartNumberField(&$aField){
        $aField['type']='string';
        $aField['singleproduct'] = true;
    }

    protected function EanField(&$aField){
        $aField['type']='string';
        $aField['singleproduct'] = true;
        $sType = $this->getInternationalIdentifier();
        $aField['i18n']['label'] = $sType;
        $aField['i18n']['hint'] = MLI18n::gi()->replace($aField['i18n']['hint'], array('Type' => $sType));
        $aField['i18n']['optional']['checkbox']['labelNegativ'] = MLI18n::gi()->replace($aField['i18n']['optional']['checkbox']['labelNegativ'], array('Type' => $sType));
    }

    protected function ImagesField(&$aField){
        $aField['type']='imagemultipleselect';

        // Get max_images from category details
        $categoryId = $this->getField('variationgroups.value', 'value');
        if (!empty($categoryId) && $categoryId !== 'none') {
            $categoryDetails = $this->callGetCategoryDetails($categoryId);
            if (!empty($categoryDetails['DATA']['max_images'])) {
                $aField['max_images'] = $categoryDetails['DATA']['max_images'];
                // Update hint text with dynamic max_images value
                $aField['i18n']['hint'] = MLI18n::gi()->replace($aField['i18n']['hint'], array('MaxImages' => $categoryDetails['DATA']['max_images']));
            } else {
                $aField['max_images'] = 9;
            }
        }
    }

    protected function DescriptionField(&$aField) {
        $aField['type']='text';
    }

    protected function b2bselltoField(&$aField) {
        $aField['values'] = $aField['i18n']['values'];
    }

    protected function shippingTimeField(&$aField) {
        $aValues = array(
            '-' => MLI18n::gi()->get('ML_AMAZON_SHIPPING_TIME_DEFAULT_VALUE'),
            '0' => MLI18n::gi()->get('ML_AMAZON_SHIPPING_TIME_SAMEDAY_VALUE')
        );

        for ($i = 1; $i < 31; $i++) {
            $aValues[$i.''] = $i;
        }

        return array(
            'type' => 'select',
            'values' => $aValues,
        );
    }

    /**
     * @param string $sCategoryId - The product type for which category details are requested.
     *
     * @return array - Returns an array of category details or an empty array in case of an error or if no details are available.
     */
    protected function callGetCategoryDetails($sCategoryId) {
        if ($sCategoryId === 'none') {
            return array();
        }
        static $details = array();
        if (isset($details[$sCategoryId])) {
            return $details[$sCategoryId];
        }
        try {
            $requestParams = array(
                'ACTION'   => 'GetCategoryDetails',
                'DATA' => array(
                    'PRODUCTTYPE' => $sCategoryId,
                    'INCLUDE_CONDITIONAL_RULES' => true
                )
            );
            $details[$sCategoryId] = MagnaConnector::gi()->submitRequestCached($requestParams, 60 * 60 * 8);
            return $details[$sCategoryId];
        } catch (MagnaException $oEx) {
            MLMessage::gi()->addDebug($oEx);
            return array();
        }
    }

    public function getMPVariationAttributes($sVariationValue) {
        $sVariationThemeValue = $this->getRequestField('variationthemecode');
        $variationTheme = null;
        if(is_array($sVariationThemeValue) && isset($sVariationThemeValue[$sVariationValue])){
            $variationTheme = $sVariationThemeValue[$sVariationValue];
        }
        $aValues = $this->callGetCategoryDetails($sVariationValue);
        $result = array();
        if ($aValues && is_array($aValues['DATA']['attributes'])) {
            foreach ($aValues['DATA']['attributes'] as $key => $value) {
                $result[$key] = array(
                    'value' => $value['title'],
                    'required' => isset($value['mandatory']) ? $value['mandatory'] : true,
                    'changed' => isset($value['changed']) ? $value['changed'] : null,
                    'desc' => isset($value['desc']) ? $value['desc'] : '',
                    'values' => !empty($value['values']) ? $value['values'] : array(),
                    'dataType' => !empty($value['type']) ? $value['type'] : 'text',
                );
            }

            $errors = MLModule::gi()->verifyItemByMarketplaceToGetMandatoryAttributes($sVariationValue, $variationTheme);
            foreach ($errors as $error) {
                if(isset($error['ERRORDATA'])) {//for generic API errors and API exceptions, there is no ERRORDATA
                    $errorData = $error['ERRORDATA'];
                    if ($error['ERRORLEVEL'] === 'FATAL' && is_array($errorData['error_categories']) && in_array('MISSING_ATTRIBUTE', $errorData['error_categories'], true) && isset($errorData['error_attributeNames']) && is_array($errorData['error_attributeNames'])) {
                        foreach ($errorData['error_attributeNames'] as $attributeName) {
                            if (isset($result[$attributeName])) {
                                $result[$attributeName]['required'] = true;
                            }
                        }

                    }
                }
            }


            $errors = MLMessage::gi()->getAll(ML_Core_Model_Message::ERROR);
            if(!empty($errors)){
                foreach ($errors as $error) {
                    if (isset($error['additional']['data'])) {//for generic API errors and API exceptions, there is no 'additional'>'data'
                        $errorData = $error['additional']['data'];
                        if (is_array($errorData['error_categories']) && in_array('MISSING_ATTRIBUTE', $errorData['error_categories'], true) && isset($errorData['error_attributeNames']) && is_array($errorData['error_attributeNames'])) {
                            foreach ($errorData['error_attributeNames'] as $attributeName) {
                                if (isset($result[$attributeName])) {
                                    $result[$attributeName]['required'] = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $aResultFromDB = $this->getAttributesFromDB($sVariationValue, $this->getCustomIdentifier());
        $additionalAttributes = array();
        $newAdditionalAttributeIndex = 0;
        $positionOfIndexInAdditionalAttribute = 2;
 
        if(is_array($aResultFromDB)) {
            foreach ($aResultFromDB as $key => $value) {
                if (strpos($key, 'additional_attribute_') === 0) {
                    $additionalAttributes[$key] = $value;
                    $aAdditionalAttributeIndex = explode('_', $key);
                    $additionalAttributeIndex = (int)$aAdditionalAttributeIndex[$positionOfIndexInAdditionalAttribute];
                    $newAdditionalAttributeIndex = ($newAdditionalAttributeIndex > $additionalAttributeIndex) ?
                        $newAdditionalAttributeIndex + 1 : $additionalAttributeIndex + 1;
                }
            }
        }

        if (count($additionalAttributes) < $this->numberOfMaxAdditionalAttributes || $this->numberOfMaxAdditionalAttributes === -1) {
            $additionalAttributes['additional_attribute_' . $newAdditionalAttributeIndex] = array();
        }

        foreach ($additionalAttributes as $attributeKey => $attributeValue) {
            $result[$attributeKey] = array(
                'value' => self::getMessage('_prepare_variations_additional_attribute_label'),
                'custom' => true,
                'required' => false,
            );
        }
                
        $this->detectChanges($result, $sVariationValue);
    
        return $result;
    }

    protected function getMPAttributeValues($sCategoryId, $sMpAttributeCode, $sAttributeCode = false) {
        $response = $this->callGetCategoryDetails($sCategoryId);
        $aValues = array();
        $fromMP = false;
        if ($response && is_array($response['DATA']['attributes'])) {
            foreach ($response['DATA']['attributes'] as $key => $attribute) {
                if ($key === $sMpAttributeCode && !empty($attribute['values'])) {
                    $aValues = $attribute['values'];
                    $fromMP = true;
                    break;
                }
            }
        }

        if (empty($aValues) && $sAttributeCode) {
            $shopValues = $this->getShopAttributeValues($sAttributeCode);
            foreach ($shopValues as $value) {
                $aValues[$value] = $value;
            }
        }

        return array(
            'values' => $aValues,
            'from_mp' => $fromMP
        );
    }

    public function triggerAfterField(&$aField, $parentCall = false) {
        //TODO Check this parent call
        parent::triggerAfterField($aField, true);
        $sName = $aField['realname'];
          
        // when top variation groups drop down is changed, its value is updated in getRequestValue
        // otherwise, it should remain empty.
        // without second condition this function will be executed recursevly because of the second line below.
        if (!isset($aField['value'])) {
            $sProductId = $this->getProductId();

            $oPrepareTable = MLDatabase::getPrepareTableInstance();
            $sShopVariationField = $oPrepareTable->getShopVariationFieldName();

            $aPrimaryCategories = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());
            $sPrimaryCategoriesValue = isset($aPrimaryCategories['[' . $sProductId . ']']) ? $aPrimaryCategories['[' . $sProductId . ']'] : reset($aPrimaryCategories);
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
                    $aValue = $this->oPrepareList->get($sShopVariationField);
                    $aValueFix = isset($aValue['[' . $sProductId . ']']) ? $aValue['[' . $sProductId . ']'] : reset($aValue);

                    // the ApplyData column is deprecated and we do not use it anymore
//                    if (empty($aValueFix)) {
//                        $aValue = $this->oPrepareList->get('ApplyData');
//                        $aValueFix = isset($aValue['[' . $sProductId . ']']) ? $aValue['[' . $sProductId . ']'] : reset($aValue);
//                        if (!empty($aValueFix['Attributes'])) {
//                            $aValueFix = $this->fixOldAttributes($aValueFix['Attributes'], $sPrimaryCategoriesValue);
//                        }
//                    }

                    // real name is in format "variationgroups.qnvjagzvcm1hda____.rm9ybwf0.code"
                    $sCustomIdentifier = count($aCustomIdentifier) == 2 ? $aCustomIdentifier[1] : '';
                    if (empty($sCustomIdentifier)) {
                        $sCustomIdentifier = $this->getCustomIdentifier();
                    }

                    $aProductType = $this->oPrepareList->get('ProductType');
                    $aProductTypeFirst = reset($aProductType);
                    $sProductType = !empty($aProductType['[' . $sProductId . ']'][$sPrimaryCategoriesValue]) ? $aProductType['[' . $sProductId . ']'][$sPrimaryCategoriesValue] : (!empty($aProductTypeFirst[$sPrimaryCategoriesValue])? $aProductTypeFirst[$sPrimaryCategoriesValue]:null);
                    if (!isset($aValueFix) || (strtolower($sPrimaryCategoriesValue) !== strtolower($aNames[1]))) {
                        // cache db values
                        $hashParams = md5($aNames[1].$sCustomIdentifier.'ShopVariation');
                        if (!array_key_exists($hashParams, $this->variationCache)) {
                            $this->variationCache[$hashParams] = $this->getVariationDb()
                                ->set('Identifier', $aNames[1])
                                ->set('CustomIdentifier', $sCustomIdentifier)
                                ->get('ShopVariation');
                        }
                        $aValue = $this->variationCache[$hashParams];
                    } else {
                        $aValue = $aValueFix;
                    }

                    if (!empty($aValue) && is_array($aValue)) {
                        foreach ($aValue as $sKey => $aMatch) {
                            if (strtolower($sKey) === $aNames[2]) {
                                $aField['value'] = $aMatch['Code'];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $sIdentifier - Amazon Main Category
     * @param $sCustomIdentifier - Product Type
     * @param null $sAttributeCode - Attribute
     * @param bool $bFreeText - If is a free text field
     *
     * @return array|mixed|string
     */
    protected function getAttributeValues($sIdentifier, $sCustomIdentifier, $sAttributeCode = null, $bFreeText = false) {
        $sProductId = $this->getProductId();

        $oPrepareTable = MLDatabase::getPrepareTableInstance();
        $sShopVariationField = $oPrepareTable->getShopVariationFieldName();
        $sPrimaryCategory = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());
        $aProductType = $this->oPrepareList->get('ProductType');
        $aProductTypeFirst = reset($aProductType);
        $sPrimaryCategoryValue = isset($sPrimaryCategory['[' . $sProductId . ']']) ? $sPrimaryCategory['[' . $sProductId . ']'] : reset($sPrimaryCategory);
        $sProductType = !empty($aProductType['[' . $sProductId . ']'][$sPrimaryCategoryValue]) ? $aProductType['[' . $sProductId . ']'][$sPrimaryCategoryValue] : (!empty($aProductTypeFirst[$sPrimaryCategoryValue]) ? $aProductTypeFirst[$sPrimaryCategoryValue] : null);
        if (!empty($sPrimaryCategory)) {
            if ($sPrimaryCategoryValue === $sIdentifier) {
                $aShopVariation = $this->oPrepareList->get($sShopVariationField);
                $aValue = isset($aShopVariation['[' . $sProductId . ']']) ? $aShopVariation['[' . $sProductId . ']'] : reset($aShopVariation);
                // the ApplyData column is deprecated we do not use it anymore
//                if (empty($aValue)) {
//                    $aShopVariation = $this->oPrepareList->get('ApplyData');
//                    $aValue = isset($aShopVariation['[' . $sProductId . ']']) ? $aShopVariation['[' . $sProductId . ']'] : reset($aShopVariation);
//                    if (!empty($aValue['Attributes'])) {
//                        $aValue = $this->fixOldAttributes($aValue['Attributes'], $sIdentifier);
//                    }
//                }
            }
        }

        if (!isset($aValue)) {
            // cache db values
            $hashParams = md5($sIdentifier.$sCustomIdentifier.'ShopVariation');
            if (!array_key_exists($hashParams, $this->variationCache)) {
                $this->variationCache[$hashParams] = $this->getVariationDb()
                    ->set('Identifier', $sIdentifier)
                    ->set('CustomIdentifier', $sCustomIdentifier)
                    ->get('ShopVariation');
            }
            $aValue = $this->variationCache[$hashParams];
        }

        if (!empty($aValue) && is_array($aValue)) {
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

    /**
     * Covering situation if client prepared item before new variation matching concept
     *
     * @param $attributes
     * @param $sCategoryId
     * @return array
     */
    private function fixOldAttributes($attributes, $sCategoryId) {
        $response = $this->callGetCategoryDetails($sCategoryId);
        $mpAttributes = empty($response)? array() : $response['DATA']['attributes'];

        $attributesFixed = array();
        foreach ($attributes as $attributeKey => $attributeValue) {
            $attributesFixed[$attributeKey] = array(
                'Kind' => 'Matching',
                'Values' => $attributeValue,
                'Required' => isset($mpAttributes[$attributeKey]['mandatory']) ? (bool)$mpAttributes[$attributeKey]['mandatory'] : false,
                'Code' => !empty($mpAttributes[$attributeKey]['values']) ? 'attribute_value' : 'freetext',
                'Error' => false
            );
        }

        return $attributesFixed;
    }
    
    /**
     * @param $sField
     * @param array $aConfig
     * @return array
     */
    private function getTopTenCategories($sField, $aConfig=array()) {
        $mpID = MLModule::gi()->getMarketPlaceId();
        $sParent = isset($aConfig[0]) ? $aConfig[0] : '';
        switch ($sField) {
            case 'topMainCategory':{
                $sWhere = "1 = 1";
                $sUnion = null;
                break;
            }
            case 'topProductType':{
                $sWhere = "topMainCategory = '".$sParent."'";
                $sUnion = null;
                break;
            }
            case 'topBrowseNode':{
                $sField = 'topBrowseNode1';
                $sWhere = "topMainCategory = '".$sParent."'";
                $sUnion = 'topBrowseNode2';
                break;
            }
        }

        if ($sUnion === null) {
            $sSql = "
                SELECT ".$sField." 
                  FROM magnalister_amazon_prepare
                 WHERE     ".$sWhere."
                       AND mpID = '".$mpID."'
                       AND ".$sField." <> '0'
              GROUP BY ".$sField." 
              ORDER BY COUNT(*) DESC
            ";
        } else {
            // if performance problems in this query, get all data and prepare with php
            $sSql="
				SELECT m.".$sField." FROM
				(
					(
						SELECT f.".$sField."
						FROM magnalister_amazon_prepare f 
						WHERE ".$sWhere." AND mpID = '".$mpID."' AND ".$sField." <> '0' 
					)
					UNION ALL
					(
						SELECT u.".$sUnion."
						FROM magnalister_amazon_prepare u 
						WHERE ".$sWhere." AND mpID = '".$mpID."' AND ".$sUnion." <> '0'
					)
				) m
				GROUP BY m.".$sField."
				ORDER BY COUNT(m.".$sField.") DESC
			";
        }

        $aTopTen = MLDatabase::getDbInstance()->fetchArray($sSql, true);
        $aOut = array();
        try {
            switch ($sField) {
                case 'topMainCategory':{
                    $aCategories = MLModule::gi()->getMainCategories();
                    break;
                }
                case 'topProductType':{
                    $aCategories = MLModule::gi()->getProductTypesAndAttributes($sParent);
                    $aCategories = $aCategories['ProductTypes'];
                    break;
                }
                case 'topBrowseNode1':{
                    $aCategories = MLModule::gi()->getBrowseNodes($sParent);
                    break;
                }
            }

            foreach($aTopTen as $sCurrent){
                if(array_key_exists($sCurrent, $aCategories)) {
                    $aOut[$sCurrent] = $aCategories[$sCurrent];
                }else{
                    MLDatabase::getDbInstance()->query("UPDATE magnalister_amazon_prepare set ".$sField." = 0 where ".$sField." = '".$sCurrent."'");//no mpid
                    if($sUnion !== null){
                        MLDatabase::getDbInstance()->query("UPDATE magnalister_amazon_prepare set ".$sUnion." = 0 where ".$sUnion." = '".$sCurrent."'");//no mpid
                    }
                }
            }
        } catch (MagnaException $e) {
            echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
        }

        asort($aOut);
        return $aOut;
    }

    private function getInternationalIdentifier() {
        $sSite = MLModule::gi()->getConfig('site');
        if ($sSite === 'US') {
            return 'UPC';
        }

        return 'EAN';
    }
    
    protected function shippingTemplateField(&$aField) {
        $aDefaultTemplate = MLModule::gi()->getConfig('shipping.template');
        $aTemplateName = MLModule::gi()->getConfig('shipping.template.name');
        $aField['type']='select';
        $aField['autooptional'] = false;
        foreach ($aDefaultTemplate as $iKey => $sValue) {
             $aField['values'][]= $aTemplateName[$iKey];
        }
    }


    /**
     * In Amazon comes all errors from Amazon so don't need to show extra message
     *
     * @param string $key The identifier for the missing free text attribute.
     * @param array $aErrors The array of errors to be returned.
     * @return array The array of errors including the added debug message.
     */
    protected function setMissingFreetextAttributesError($key, array $aErrors) {
        MLMessage::gi()->addDebug($key . self::getMessage('_prepare_variations_error_free_text'));
        return $aErrors;
    }

    /**
     * In Amazon comes all errors from Amazon so don't need to show extra message
     *
     * @param $sAttributeName
     * @param array $aErrors
     * @return array
     */
    protected function setMissingRequiredAttrbiteError($sAttributeName, array $aErrors) {
        MLMessage::gi()->addDebug(self::getMessage('_prepare_variations_error_text', array('attribute_name' => $sAttributeName)));
        return $aErrors;
    }

    /**
     * Get conditional rules from category details API response
     *
     * @param string $categoryId The category ID
     * @return array Array of conditional rules
     */
    protected function getCategoryConditionalRules($categoryId) {
        if (empty($categoryId) || $categoryId === 'none' || $categoryId === 'new') {
            return array();
        }

        try {
            // Get category details from API
            $categoryDetails = $this->callGetCategoryDetails($categoryId);

            // Extract conditional rules from category details
            if (isset($categoryDetails['DATA']['conditional_rules']) && is_array($categoryDetails['DATA']['conditional_rules'])) {
                return $categoryDetails['DATA']['conditional_rules'];
            }

            // Check if it exists under a different key structure
            if (isset($categoryDetails['DATA']['category_data']['conditional_rules'])) {
                return $categoryDetails['DATA']['category_data']['conditional_rules'];
            }

        } catch (Exception $e) {
            MLMessage::gi()->addDebug('Error fetching conditional rules for category ' . $categoryId . ': ' . $e->getMessage());
        }

        return array();
    }

    /**
     * Get category attributes for conditional rules processing
     *
     * @param string $categoryId The category ID
     * @return array Array of category attributes
     */
    protected function getCategoryAttributesForRules($categoryId) {
        if (empty($categoryId) || $categoryId === 'none' || $categoryId === 'new') {
            return array();
        }

        try {
            // Get category details from API
            $categoryDetails = $this->callGetCategoryDetails($categoryId);

            // Extract attribute definitions (with values) from category details
            $attributes = array();

            // Get attributes from main category data with their values
            if (isset($categoryDetails['DATA']['attributes']) && is_array($categoryDetails['DATA']['attributes'])) {
                foreach ($categoryDetails['DATA']['attributes'] as $attrName => $attrData) {
                    if (is_array($attrData) && isset($attrData['values'])) {
                        $attributes[$attrName] = $attrData;
                    }
                }
            }

            // Get variation attributes if available
            if (isset($categoryDetails['DATA']['variation_details']) && is_array($categoryDetails['DATA']['variation_details'])) {
                foreach ($categoryDetails['DATA']['variation_details'] as $theme) {
                    if (isset($theme['attributes']) && is_array($theme['attributes'])) {
                        foreach ($theme['attributes'] as $attrName) {
                            // Get the full attribute definition from the main attributes list
                            if (isset($categoryDetails['DATA']['attributes'][$attrName])) {
                                $attributes[$attrName] = $categoryDetails['DATA']['attributes'][$attrName];
                            }
                        }
                    }
                }
            }

            return $attributes;

        } catch (Exception $e) {
            MLMessage::gi()->addDebug('Error fetching category attributes for conditional rules for category ' . $categoryId . ': ' . $e->getMessage());
        }

        return array();
    }
}
