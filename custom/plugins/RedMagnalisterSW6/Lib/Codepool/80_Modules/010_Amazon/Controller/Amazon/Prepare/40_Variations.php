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

MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_VariationsAbstract');

class ML_Amazon_Controller_Amazon_Prepare_Variations extends ML_Form_Controller_Widget_Form_VariationsAbstract {
    public function __construct() {
        parent::__construct();

        MLSettingRegistry::gi()->addJs(array(
                'react/dist/magnalister-amazon-variations-bundle.umd.js')
        );
        MLSettingRegistry::gi()->addCss(array(
                'react/dist/style.css')
        );
    }
    /**
     * Populates the provided field array with options for variation groups.
     *
     * @param array $aField Reference to the field array that will be updated with variation group options.
     *                      The method updates the 'values' key in this array by merging a predefined default option
     *                      with the available categories and the last three used categories.
     *
     * @return void This method does not return a value. It modifies the provided field array directly.
     */
    protected function variationGroups_ValueField(&$aField)
    {
        $allCategories = MLModule::gi()->getMainCategories();

        // Get the last three used categories
        $lastUsed = MLDatabase::getDbInstance()->fetchArray("
            SELECT Identifier
              FROM magnalister_amazon_variantmatching
          ORDER BY ModificationDate DESC
             LIMIT 3
        ", true);
        if (!is_array($lastUsed)) {
            $lastUsed = array();
        }
        foreach ($lastUsed as $key => $value) {
            unset($lastUsed[$key]);
            if (array_key_exists($value, $allCategories)) {
                $lastUsed[$value] = $allCategories[$value];
            }
        }

        if (count($lastUsed) > 0) {
            $values = array($this->__('ML_TOPTEN_TEXT') => $lastUsed) + array($this->__('ML_LABEL_CATEGORY') => $allCategories);
        } else {
            $values = $allCategories;
        }

        $aField['values'] = array_merge(
            array('none' => MLI18n::gi()->get('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT')),
            $values
        );
    }

    protected function variationMatchingField(&$aField)
    {
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
            'field' => array(
                'type' => 'switch',
            ),
        );
    }

    /**
     * Retrieves detailed information for a specific category based on the provided category identifier.
     *
     * @param string $sCategoryId The identifier of the category for which details are requested.
     *                             If the value is 'none', an empty array is returned.
     *
     * @return array The response from the MagnaConnector containing the category details.
     *               If the category identifier is 'none', an empty array is returned.
     * @throws MagnaException
     */
    protected function callGetCategoryDetails($sCategoryId) {
        static $aCache = array();
        if (isset($aCache[$sCategoryId])) {
            return $aCache[$sCategoryId];
        }

        if ($sCategoryId === 'none') {
            return array();
        }

        $requestParams = array(
            'ACTION' => 'GetCategoryDetails',
            'DATA' => [
                'PRODUCTTYPE' => $sCategoryId
            ]
        );

        $aCache[$sCategoryId] = MagnaConnector::gi()->submitRequestCached($requestParams);
        return $aCache[$sCategoryId];
    }

    public function getMPVariationAttributes($sVariationValue)
    {
        $aValues = $this->callGetCategoryDetails($sVariationValue);
        $result = array();
        if ($aValues && isset($aValues['DATA']['attributes']) && is_array($aValues['DATA']['attributes'])) {
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
            $errors = MLModule::gi()->verifyItemByMarketplaceToGetMandatoryAttributes($sVariationValue);
            foreach ($errors as $error) {
                $errorData = $error['ERRORDATA'];
                if ($error['ERRORLEVEL'] === 'FATAL' && in_array('MISSING_ATTRIBUTE', $errorData['error_categories'], true) && isset($errorData['error_attributeNames']) && is_array($errorData['error_attributeNames'])) {
                    foreach ($errorData['error_attributeNames'] as $attributeName) {
                        if (isset($result[$attributeName])) {
                            $result[$attributeName]['required'] = true;
                        }
                    }

                }
            }
        }

        $this->checkAttributesFromDB($sVariationValue, isset($aValues['DATA']['name']) ? $aValues['DATA']['name'] : '');

        $aResultFromDB = $this->getAttributesFromDB($sVariationValue);
        $additionalAttributes = array();
        $newAdditionalAttributeIndex = 0;
        $positionOfIndexInAdditionalAttribute = 2;

        if ($aResultFromDB) {
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
            $result[$attributeKey] =  array(
                'value' => self::getMessage('_prepare_variations_additional_attribute_label'),
                'required' => false,
            );
        }

        return $result;
    }

    protected function getMPAttributeValues($sCategoryId, $sMpAttributeCode, $sAttributeCode = false)
    {
        $response = $this->callGetCategoryDetails($sCategoryId);
        $fromMP = false;
        foreach ($response['DATA']['attributes'] as $key => $attribute) {
            if ($key === $sMpAttributeCode && !empty($attribute['values'])) {
                $aValues = $attribute['values'];
                $fromMP = true;
                break;
            }
        }

        if (!isset($aValues)) {
            if ($sAttributeCode) {
                $shopValues = $this->getShopAttributeValues($sAttributeCode);
                foreach ($shopValues as $value) {
                    $aValues[$value] = $value;
                }
            }
        }

        return array(
            'values' => isset($aValues) ? $aValues : array(),
            'from_mp' => $fromMP
        );
    }

    public function saveAction($blExecute = true) {
        if ($blExecute) {
            $aActions = $this->getRequest($this->sActionPrefix);
            $savePrepare = $aActions['saveaction'] === '1';
            $aMatching = $this->getRequestField();
            $sIdentifier = $aMatching['variationgroups.value'];
            if ($sIdentifier === 'none') {
                MLMessage::gi()->addError(self::getMessage('_prepare_match_variations_no_selection'));
            } else {
                if ($savePrepare) {
                    MLRequest::gi()->set('resetForm', true);
                    MLMessage::gi()->addSuccess(self::getMessage('_prepare_variations_saved'));
                }

            }
        }
    }
}
