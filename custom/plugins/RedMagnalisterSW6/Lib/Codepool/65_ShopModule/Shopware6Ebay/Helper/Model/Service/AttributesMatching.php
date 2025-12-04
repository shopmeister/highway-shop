<?php
/**
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
 * $Id$
 *
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLFilesystem::gi()->loadClass('Ebay_Helper_Model_Service_AttributesMatching');
class ML_Shopware6Ebay_Helper_Model_Service_AttributesMatching extends ML_Ebay_Helper_Model_Service_AttributesMatching {


    /**
     * Based on AM matching configuration for attribute and shop attribute value, locates matched MP value and returns it.
     * Empty string will be returned  when no matching is found
     *
     * @param array $shopAttribute Shop attribute configuration with value for attribute in shop
     * @param array $matchedAttribute AM matching configuration for attribute
     *
     * @return array|mixed|string Matched MP attribute value for specific shop attribute value.
     */
    protected function findMatchingAttributeValue($shopAttribute, $matchedAttribute)
    {
        //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($shopAttribute,$matchedAttribute));
        if (!isset($shopAttribute['value'])) {
            return '';
        }

        // For shop text attributes take directly shop value, for other go through matched values and find matched value
        $class = MLFormHelper::getShopInstance();
        if (!$this->valueIsEmpty($shopAttribute['value']) && ($shopAttribute['type'] === $class::Shop_Attribute_Type_Key_Text)) {
            return $shopAttribute['value'];
        }
         //magnalister already separate character by ',,' to prevent separate character that contains ','
        if (is_array($shopAttribute['value'])) {
            $MultiplyPropertiesStringValueConvertToArray = $shopAttribute['value'];
        } else {
            $MultiplyPropertiesStringValueConvertToArray = explode(',,', $shopAttribute['value']);
        }
        $MultiplyValueString = $shopAttribute['value'];
        $multiplyattributeValue = '';
        foreach ($MultiplyPropertiesStringValueConvertToArray as $value) {
            $shopAttribute['value'] = $value;
            //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($shopAttribute, $matchedAttribute));
            $attributeValue = $this->findMatchingMPValue($shopAttribute, $matchedAttribute);
            //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($attributeValue));
            if (!$attributeValue) {
                MLMessage::gi()->addDebug('the value is not match by the customer', array($value));
                break;
            } else {
                $multiplyattributeValue .= (($multiplyattributeValue === '') ? '' : ',') . $attributeValue;
            }
        }
        $mReturn = $this->convertMatchedAttributeValue($matchedAttribute, $multiplyattributeValue);
        //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($mReturn));
        return $mReturn;
    }

}
