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

class ML_Cdiscount_Model_Service_AddItems extends ML_Modul_Model_Service_AddItems_Abstract {

    protected function getProductArray() {
        $aMasterProducts = $this->indexingProductData();
        $aDataToBeUpload = $this->processingVariationData($aMasterProducts);
        return $aDataToBeUpload;
    }

    protected function hasAttributeMatching() {
        return true;
    }

    protected function createVariantMasterProducts($variantProducts, $variationMasterItemTitle, $variationMasterSku, $productToClone) {
        $variationProducts = array();
        foreach ($variantProducts as $variation) {
            $variation['ParentSKU'] = $variationMasterSku;
            $variation['Title'] = $variationMasterItemTitle;
            $variation['IsSplit'] = intval($variationMasterSku != $productToClone['SKU']);
            unset($variation['ItemTitle'], $variation['Variation']);
            $this->unsetShopRawData($variation);
            $variationProducts[] = $variation;
        }

        return $variationProducts;
    }


    protected function indexingProductData() {
        /* @var $oHelper ML_Cdiscount_Helper_Model_Service_Product */
        $oHelper = MLHelper::gi('Model_Service_Product');
        $aMasterProducts = array();
        $aPreparedImageExcludeVariantImages = array();
        foreach ($this->oList->getList() as $oProduct) {
            $sParentSku = $oProduct->getMarketPlaceSku();
            $oHelper->setProduct($oProduct);
            foreach ($this->oList->getVariants($oProduct) as $oVariant) {
                /* @var $oVariant ML_Shop_Model_Product_Abstract */
                if ($this->oList->isSelected($oVariant)) {
                    $oHelper->resetData();
                    $variationData = $oHelper->setVariant($oVariant)->getData();
                    $aPreparedImageExcludeVariantImages = $this->gatheringGenericImages($aPreparedImageExcludeVariantImages, $sParentSku, $variationData);
                    unset($variationData['PreparedImages']);
                    $aMasterProducts[$sParentSku][$oVariant->get('id')] = $variationData;

                }
            }
            foreach ($aMasterProducts[$sParentSku] as &$aVariantProduct) {
                if (isset($aPreparedImageExcludeVariantImages[$sParentSku])) {
                    $aVariantProduct['Images'] = array_merge(
                        isset($aVariantProduct['Images']) && is_array($aVariantProduct['Images'])
                            ? $aVariantProduct['Images']
                            : array(),
                        $aPreparedImageExcludeVariantImages[$sParentSku]
                    );
                    $aImages = array();
                    foreach ($aVariantProduct['Images'] as &$image) {
                        $aImages[] = array('URL' => $image);
                    }
                    $aVariantProduct['Images'] = $aImages;
                }
            }
        }
        return $aMasterProducts;
    }

    protected function processingVariationData($aMasterProducts) {
        $aDataToUpload = array();
        foreach ($aMasterProducts as $sParentSku => $variationData) {
            foreach ($variationData as $variantId => $variationDatum) {
                if ($variationDatum['SKU'] === $sParentSku) {
                    $variationDatum['ParentSKU'] = $sParentSku;
                    $aDataToUpload[$variantId] = $variationDatum;
                    continue;
                }
                $aDataToUpload[$variantId] = $variationDatum;
                $aDataToUpload[$variantId]['SKU'] = $sParentSku;
                $variationDatum['Variation'] = $variationDatum;
                $aDataToUpload[$variantId]['Variations'][] = $variationDatum;
            }
        }
        return $aDataToUpload;
    }

    protected function gatheringGenericImages($aPreparedImageExcludeVariantImages, $sParentSku, $variationData) {
        if (!isset($aPreparedImageExcludeVariantImages[$sParentSku])) {//initial prepared image
            // Backwards compatibility for PHP 8
            // PreparedImages could be null, which turns into a Fatal Error on the array_diff() call below
            if (empty($variationData['PreparedImages'])) {
                $aPreparedImageExcludeVariantImages[$sParentSku] = null;

                return $aPreparedImageExcludeVariantImages;
            }

            $aPreparedImageExcludeVariantImages[$sParentSku] = $variationData['PreparedImages'];
        }
        //remove variant image from prepared image
        $aPreparedImageExcludeVariantImages[$sParentSku] = array_diff(
            $aPreparedImageExcludeVariantImages[$sParentSku],
            isset($variationData['Images']) && is_array($variationData['Images'])
                ? $variationData['Images']
                : array()
        );
        return $aPreparedImageExcludeVariantImages;
    }

    protected function shouldSendShopData() {
        return true;
    }

    /**
     * Processes each variation one by one
     *
     * @param $product
     * @return false|mixed
     */
    protected function setProductVariationValues($product) {
        $variants = $this->createVariantMasterProducts(
            $product['Variations'], $product['Title'],
            $product['SKU'], current($product['Variations'])
        );

        $product = current($variants);

        unset($product['Variations']);

        return $product;
    }

    /**
     * remove master and variation with quantity <= 0
     * @return boolean
     */
    protected function checkQuantity() {
        $quantityPerParentSKUs = array();
        foreach ($this->aData as $sKey => $aItem) {
            if (!array_key_exists('ParentSKU', $aItem)) {
                $aItem['ParentSKU'] = '';
            } else {
                // set the total quantity for variation products
                $quantityPerParentSKUs[$aItem['ParentSKU']] = isset($quantityPerParentSKUs[$aItem['ParentSKU']]) ?
                    $quantityPerParentSKUs[$aItem['ParentSKU']] + $aItem['Quantity'] : $aItem['Quantity'];
            }
            if (isset($aItem['Quantity']) && ((int)$aItem['Quantity']) <= 0) {
                if ($aItem['ParentSKU'] == $aItem['SKU']) { // Simple Product
                    $sMessage = MLI18n::gi()->get('sAddItemProductWithZeroQuantity');
                    MLMessage::gi()->addWarn($sMessage, '', false);

                    $oProduct = MLProduct::factory()->getByMarketplaceSKU($aItem['SKU'], true);
                    if (!$oProduct->existsMlProduct()) {
                        $oProduct = MLProduct::factory()->getByMarketplaceSKU($aItem['SKU']);
                    }
                    $iProductId = $oProduct->get('id');
                    MLErrorLog::gi()->addError($iProductId, $aItem['SKU'], $sMessage, array('SKU' => $aItem['SKU']));
                    $this->aError[] = $sMessage;
                    unset($this->aData[$sKey]);
                } else { // Variation Product
                    $sMessage = MLI18n::gi()->get('sAddItemProductWithZeroQuantity');
                    MLMessage::gi()->addWarn($sMessage, '', false);
                    unset($this->aData[$sKey]);

                }
            }
        }
        // check if there are variation products that have 0 quantity in all variations
        $zeroQuantityParentSKUs = array_filter($quantityPerParentSKUs, function ($value) {
            return $value <= 0;
        });

        // add error message for variation products with 0 quantity in all variations
        foreach ($zeroQuantityParentSKUs as $parentSKU => $quantity) {
            $sMessage = MLI18n::gi()->get('sAddItemProductWithZeroQuantity');
            MLMessage::gi()->addWarn($sMessage, '', false);
            $oProduct = MLProduct::factory()->getByMarketplaceSKU($parentSKU, true);
            MLErrorLog::gi()->addError($oProduct->get('id'), $parentSKU, $sMessage, array('SKU' => $parentSKU));
            $this->aError[] = $sMessage;
        }

        return !empty($this->aData);
    }
}
