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

class ML_Amazon_Model_Service_AddItems extends ML_Modul_Model_Service_AddItems_Abstract {

    protected $mandatoryAttributeMode = false;
    public function setMandatoryAttributeMode($mandatoryAttributeMode) {
        $this->mandatoryAttributeMode = $mandatoryAttributeMode;
    }

    protected function getProductArray() {
        /* @var $oHelper ML_Amazon_Helper_Model_Service_Product */
        $oHelper = MLHelper::gi('Model_Service_Product');
        $oHelper->setMandatoryAttributeMode($this->mandatoryAttributeMode);
        $aMasterProducts = array();
        foreach ($this->oList->getList() as $oProduct) {
            /* @var $oProduct ML_Shop_Model_Product_Abstract */
            $oHelper->setProduct($oProduct);
            foreach ($this->oList->getVariants($oProduct) as $oVariant) {
                /* @var $oVariant ML_Shop_Model_Product_Abstract */
                if ($this->oList->isSelected($oVariant)) {
                    $oHelper->addVariant($oVariant);
                }
            }
            try {
                if (
                    $oHelper->getPrepareType() === 'apply'
                    || $this->sAction === 'VerifyAddItems'
                ) {//master-variant structure
                    $aMasterProducts[$oProduct->get('id')] = $oHelper->getData();
                } else {
                    $aHelperData = $oHelper->getData();
                    // Check if getData() returned a single product (associative array with 'Id' key)
                    // or multiple products (numeric array of products)
                    if (isset($aHelperData['Id'])) {
                        // Single product case - wrap it in an array for consistent handling
                        $aHelperData = array($aHelperData);
                    }
                    foreach ($aHelperData as $aData) {
                        $aMasterProducts[$aData['Id']] = $aData;
                        unset($aMasterProducts[$aData['Id']]['Id']);
                    }
                }
            } catch (Exception $oEx) {
                MLMessage::gi()->addDebug($oEx);
            }
            //Replace Variations 'images' add item field with customized variations 'prepared' field that contain (prepared variations images + exclusive master product images that no assign to any variation products)
            if (!empty($aMasterProducts[$oProduct->get('id')]['Variations'])) { // only for existing product IDs, otherwise the array field is implied
                foreach ($aMasterProducts[$oProduct->get('id')]['Variations'] as &$aVariantProduct) {
                    if (!empty($aVariantProduct['PreparedImages'])) {
                        $aVariantProduct['Images'] = $aVariantProduct['PreparedImages'];
                        //Unset and remove PreparedImages field after replacing images with preparedImages field there is no need to send it in AddItem
                        unset($aVariantProduct['PreparedImages']);
                    }
                }
            }
        }

        return $aMasterProducts;
    }

    /**
     * amazon can add item with quantity <= 0
     * @return boolean
     */
    protected function checkQuantity() {
        return true;
    }

    protected function handleException($oEx) {
        $mError = $oEx->getErrorArray();
        foreach ($mError['ERRORS'] as $aError) {


            $aActions = MLRequest::gi()->data('action');
            $savePrepare = isset($aActions['prepareaction']) && $aActions['prepareaction'] === '1';
            if ($savePrepare) {
                $errorData = isset($aError['ERRORDATA']) ? $aError['ERRORDATA'] : array();
                $errorMessage = $this->convertAttributeKeysToScrollLinks($aError['ERRORMESSAGE']);
                MLMessage::gi()->addError('Amazon: '.$errorMessage, $errorData, false);
                MLMessage::gi()->addDebug($aError['ERRORMESSAGE']);
            }
            $this->aError[] = $aError['ERRORMESSAGE'];
        }
    }

    /**
     * Converts attribute keys in error messages to clickable scroll links.
     * Parses patterns like "(Attribute: key1, key2, key3)" and wraps each key
     * in an anchor tag that scrolls to the corresponding attribute row in React component.
     *
     * If the attribute row doesn't exist (optional attribute not yet added),
     * it will automatically add the attribute via React's magnalisterAddOptionalAttribute()
     * function, then scroll to it.
     *
     * @param string $message The error message containing attribute keys
     * @return string The message with attribute keys converted to clickable links
     */
    protected function convertAttributeKeysToScrollLinks($message) {
        // Pattern to match (Attribute: key1, key2, ...) or (Attribute: key1)
        $pattern = '/\(Attribute:\s*([^)]+)\)/';

        return preg_replace_callback($pattern, function($matches) {
            $attributesPart = $matches[1];
            // Split by comma and process each attribute key
            $keys = array_map('trim', explode(',', $attributesPart));
            $linkedKeys = array();

            foreach ($keys as $key) {
                if (!empty($key)) {
                    $escapedKey = htmlspecialchars($key);

                    // Build onclick handler:
                    // 1. If element exists -> scroll to it
                    // 2. If not exists -> add as optional attribute via React, then scroll
                    $onclick = "(function(e){"
                        . "e.preventDefault();"
                        // Helper function to scroll and highlight
                        . "function scrollHL(el){"
                        . "el.scrollIntoView({behavior:'smooth',block:'center'});"
                        . "el.style.animation='ml-attr-highlight 0.4s ease-in-out 6';"
                        . "setTimeout(function(){el.style.animation='';},2500);"
                        . "}"
                        // Check if element exists
                        . "var el=document.getElementById('attr-row-" . $escapedKey . "');"
                        . "if(el){"
                        . "scrollHL(el);"
                        . "}else if(typeof window.magnalisterAddOptionalAttribute==='function'){"
                        // Add optional attribute, then scroll in callback
                        . "window.magnalisterAddOptionalAttribute('" . $escapedKey . "',function(){"
                        . "setTimeout(function(){"
                        . "var newEl=document.getElementById('attr-row-" . $escapedKey . "');"
                        . "if(newEl){scrollHL(newEl);}"
                        . "},150);"
                        . "});"
                        . "}"
                        . "})(event);return false;";

                    $linkedKeys[] = '<a href="#attr-row-' . $escapedKey . '" '
                        . 'class="ml-js-noBlockUi ml-attribute-scroll-link" '
                        . 'data-attribute-key="' . $escapedKey . '" '
                        . 'onclick="' . $onclick . '"'
                        . 'style="text-decoration:underline;">'
                        . $escapedKey . '</a>';
                }
            }

            return '(Attribute: ' . implode(', ', $linkedKeys) . ')';
        }, $message);
    }
     /**
     * Retrieves additional request parameters for the given request.
     * @return array
     */
    protected function getAdditionalRequestParams() {
        return array('VERSION' => '2');
    }
    public function setValidationMode($blValidation) {
        $this->sAction = $blValidation ? 'VerifyAddItems' : 'AddItems';
        return $this;
    }

}
