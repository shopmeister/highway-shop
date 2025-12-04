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
MLFilesystem::gi()->loadClass('Modul_Helper_Model_Service_AttributesMatching');

class ML_Etsy_Helper_Model_Service_AttributesMatching extends ML_Modul_Helper_Model_Service_AttributesMatching {


    /**
     * @param $product ML_Shop_Model_Product_Abstract
     * @param $attributesToUpload
     * @param $marketplaceAttributeCode
     * @param $attributeData
     * @param $attributeName
     * @param $attributeValue
     * @return mixed
     */
    protected function convertSingleProductMatchingToNameValueManipulate($product, $attributesToUpload, $marketplaceAttributeCode, $attributeData, $attributeName, $attributeValue) {
        //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($attributesToUpload,$attributeData, $attributeName, $attributeValue));
        $propertyId = $marketplaceAttributeCode;
        if (!empty($attributeData['AttributeId'])) {
            $propertyId = $attributeData['AttributeId'];
        }
        $values = array();
        $blAttributeIsNameValue = false;
        if (    strpos($attributeValue, '-') !== false
             && isset($attributeData['Kind'])
             && $attributeData['Kind'] === 'Matching'
           ) {
            $aAttrNameValue = explode('-', $attributeValue);
            if (    is_numeric($aAttrNameValue[0])
                 && is_numeric($aAttrNameValue[1])) {
                $blAttributeIsNameValue = true;
            }
        }
        if ($blAttributeIsNameValue) {
            $propertyId = $aAttrNameValue[0];
            $valueIds = $aAttrNameValue[1];
            if (count($aAttrNameValue) > 2) {
                $values = $aAttrNameValue[2];
                $values = array($values);
            }
            $valueIds = array($valueIds);
        } else {
            if (isset($attributeData['UseShopValues']) && $attributeData['UseShopValues']) {
                $valueIds = array();
            } else {
                $valueIds = array($attributeValue);
            }
            $values = array($attributeValue);
        }
        $shopVariantAttribute = array();
        foreach ($product->getVariatonDataOptinalField(array('value', 'code', 'name')) as $aVariationData) {
            if ($attributeData['Code'] === $aVariationData['code']) {
                $shopVariantAttribute = array(
                    'value' => $aVariationData['value'],
                    'name'  => $aVariationData['name'],
                );
            }
        }
        $values = empty($values) && isset($shopVariantAttribute['value']) ? $shopVariantAttribute['value'] : $values;
        $aEtsyAttribute = array(
            'property_id'   => $propertyId,
            'value_ids'     => $valueIds,
            'property_name' => ucfirst($attributeName),
            'values'        => is_array($values) ? $values : array(),
        );
        if (isset($shopVariantAttribute[$attributeData['Code']])) {
            $attributeValue = $shopVariantAttribute[$attributeData['Code']];
        }
        $attributeGroups = MLFormHelper::getShopInstance()->getFlatShopAttributesForMatching();
        $sShopAttributeGroupCode = $attributeData['Code'];
        if (isset($attributeGroups[$sShopAttributeGroupCode]['name'])) {
            $attributeName = $attributeGroups[$sShopAttributeGroupCode]['name'];
        }
        //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($attributeName, $attributeGroups, $sShopAttributeGroupCode));

        $aEtsyAttribute = $this->checkEtsyCustomAttribute($aEtsyAttribute, $attributeValue, $attributeName, $marketplaceAttributeCode);
        $attributesToUpload[$aEtsyAttribute['property_name']] = $aEtsyAttribute;
        return $attributesToUpload;
    }

    protected function checkEtsyCustomAttribute($attributeData, $attributeValue, $attributeName, $marketplaceAttributeCode) {
        if ($marketplaceAttributeCode === 'Custom1') {
            $attributeData = array(
                'property_id'   => 513,
                'value_ids'     => array(),
                'property_name' => $attributeName,
                'values'        => array($attributeValue)
            );
        } else if ($marketplaceAttributeCode === 'Custom2') {
            $attributeData = array(
                'property_id'   => 514,
                'value_ids'     => array(),
                'property_name' => $attributeName,
                'values'        => array($attributeValue),
            );
        }
        return $attributeData;
    }

}
