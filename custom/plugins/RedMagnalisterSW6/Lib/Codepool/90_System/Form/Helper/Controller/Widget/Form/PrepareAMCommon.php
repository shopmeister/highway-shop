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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
class ML_Form_Helper_Controller_Widget_Form_PrepareAMCommon {

    /**
     *
     * @param string $sAttributeCode
     * @return array
     */
    public function getShopAttributeValues($sAttributeCode) {
        $shopValues = MLFormHelper::getShopInstance()->getPrefixedAttributeOptions($sAttributeCode);
        if (!isset($shopValues) || empty($shopValues)) {
            $shopValues = MLFormHelper::getShopInstance()->getAttributeOptions($sAttributeCode);
        }

        return $shopValues;
    }


    /**
     *
     * @param array $aFields
     * @param string $sFirst
     * @param array $aNameWithoutValue
     * @param string $sLast
     * @param array $aField
     * @return mixed
     */
    public function getSelector($aFields, $sFirst, $aNameWithoutValue, $sLast, &$aField) {
        return $aFields[$sFirst.'.'.strtolower($aNameWithoutValue[1]).'.'.strtolower($sLast).'.code']['id'];
    }

    /**
     * Detects if matched attribute is deleted on shop.
     * @param array $savedAttribute
     * @param string $warningMessageCode message code that should be displayed
     * @return boolean
     */
    public function detectIfAttributeIsDeletedOnShop($savedAttribute, &$warningMessageCode) {
        if (!isset($savedAttribute['Code'])) {
            // this will happen only if attribute was matched and then it was deleted from the shop
            $savedAttribute['Code'] = '';
        }

        if (
            $savedAttribute['Code'] === '' ||
            $savedAttribute['Code'] === 'freetext' ||
            $savedAttribute['Code'] === 'attribute_value' ||
            $savedAttribute['Code'] === 'notmatch'
        ) {
            return false;
        }

        $shopAttributes = MLFormHelper::getShopInstance()->getFlatShopAttributesForMatching();

        if (!isset($shopAttributes[$savedAttribute['Code']])) {
            $warningMessageCode = '_varmatch_attribute_deleted_from_shop';
            return true;
        }

        if (isset($savedAttribute['Values']) && is_array($savedAttribute['Values'])) {
            $shopAttributeValues = $this->getShopAttributeValues($savedAttribute['Code']);
            foreach ($savedAttribute['Values'] as $savedAttributeValue) {

                // If attribute is not an array that means that it has single value. It is explicitly casted to
                // an array and then checking function is the same both for single and multi values.
                if (!is_array($savedAttributeValue['Shop']['Key'])) {
                    $savedAttributeValue['Shop']['Key'] = array($savedAttributeValue['Shop']['Key']);
                }

                $missingShopValueKeys = array_diff_key(array_flip($savedAttributeValue['Shop']['Key']), $shopAttributeValues);
                //                MLMessage::gi()->addWarn(microtime(true).'-'.$savedAttribute['AttributeName'],$missingShopValueKeys);

                if (count($missingShopValueKeys) > 0) {

                    $warningMessageCode = '_varmatch_attribute_value_deleted_from_shop';
                    return true;
                }
            }
            return false;
        }

        return false;
    }



    public function getCategoryDetails($sCategoryId) {
        $aCategoryDetails = MagnaConnector::gi()->submitRequestCached(array(
            'ACTION' => 'GetCategoryDetails',
            'DATA' => array('CategoryID' => $sCategoryId),
        ));
        if (MLModule::gi()->isNeededPackingAttrinuteName()) {
            $aCodedKeys = array();
            $attributes = isset($aCategoryDetails['DATA']['attributes']) && isset($aCategoryDetails['DATA']['attributes']) ? $aCategoryDetails['DATA']['attributes'] : array();
            foreach ($attributes as $aCategoryDetail) {
                $aCodedKeys[current(unpack('H*', $aCategoryDetail['name']))] = $aCategoryDetail;
            }
            $aCategoryDetails['DATA']['attributes'] = $aCodedKeys;
        }
        return $aCategoryDetails;
    }

    public function getMPAttributeCode($aParentValue, $aField) {
        return key($aParentValue);
    }


    public function getSName($aName, $aField, $sMPAttributeCode) {
        return 'field['.implode('][', $aName).'][Values]';
    }

    public function shouldCheckOtherIdentifier() {
        return true;
    }

    public function addExtraInfo(&$aField) {

    }

    /**
     * @param $values array
     * @return array
     */
    public function getManipulateMarketplaceAttributeValues($values) {
        return $values;
    }

    /**
     * @param $aMatchedAttributes
     * @return mixed
     * @throws MLAbstract_Exception
     */
    public function validateMatchedAttributes($aMatchedAttributes) {
        return null;
    }

    /**
     * @return array
     * e.g.
     * {
     *     "Variations": {
     *        "a_2": {
     *           "name": "Color",
     *           "type": "select"
     *        },
     *        "a_1": {
     *            "name": "Size",
     *            "type": "select"
     *         },
     *         "optGroupClass": "variation"
     *     },
     *     "Product default fields": {
     *         "condition": {
     *             "name": "Condition",
     *             "type": "select"
     *         },
     *         "description": {
     *             "name": "Description",
     *             "type": "text"
     *         },
     *         "optGroupClass": "default"
     *     },
     *     "Properties": {
     *         "f_1": {
     *             "name": "Composition",
     *             "type": "selectAndText"
     *         },
     *         "f_2": {
     *             "name": "Property",
     *             "type": "selectAndText"
     *         },
     *         "optGroupClass": "property"
     *     }
     * }
     */
    public function getSortedShopAttributes() {
        $attributeGroups = MLFormHelper::getShopInstance()->getGroupedAttributesForMatching();
        foreach ($attributeGroups as &$attributeGroup) {
            if (is_array($attributeGroup)) {
                // Filter out non-array items from the subarray
                $onlyArrays = array_filter($attributeGroup, 'is_array');

                if (!empty($onlyArrays)) {
                    // Sort only the associative arrays
                    array_multisort(array_column($onlyArrays, 'name'), SORT_ASC, $onlyArrays);

                    // Merge sorted arrays back with the non-array items
                    $nonArrays = array_filter($attributeGroup, function ($item) {
                        return !is_array($item);
                    });
                    $attributeGroup = array_merge($nonArrays, $onlyArrays);
                }
            }
        }
        return $attributeGroups;
    }

    /**
     * @param $variationGroup
     * @param $attributeName
     * @return int|mixed|string|null
     */
    public function getVariationGroup($variationGroup, $attributeName) {
        if (is_array($variationGroup)) {
            reset($variationGroup);
            $variationGroup = key($variationGroup);
        }
        if ($attributeName !== null) {
            $variationGroup = $attributeName;
        }

        if (strpos($variationGroup, ':') !== false) {
            $variationGroup = explode(':', $variationGroup);
            $variationGroup = $variationGroup[0];
        }
        return $variationGroup;
    }

    function initializeShopAttributeSelections($aShopAttributes) {
        $aShopCustomAttributes = $aShopAttributes;
        $aShopCustomAttributes['']=$aShopAttributes[''] =MLI18n::gi()->form_select_option_firstoption;
// Add additional options to shop attributes
        $marketplaceName = MLModule::gi()->getMarketPlaceName();
        $aShopAttributes[MLI18n::gi()->get('attributes_matching_additional_options')] = array(
            'freetext'        => array('name' => MLI18n::gi()->get($marketplaceName . '_prepare_variations_free_text')),
            'attribute_value' => array('name' => MLI18n::gi()->get($marketplaceName . '_prepare_variations_choose_mp_value')),
            'optGroupClass'   => 'additionalOptions'
        );
        $aShopCustomAttributes[MLI18n::gi()->get('attributes_matching_additional_options')] = array(
            'freetext'      => array('name' => MLI18n::gi()->get($marketplaceName . '_prepare_variations_free_text')),
            'optGroupClass' => 'additionalOptions'
        );
        return array($aShopCustomAttributes ,$aShopAttributes);
    }
}
