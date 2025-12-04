<?php

MLFilesystem::gi()->loadClass('Shopware6_Model_ProductList_Abstract');

/**
 * select all products 
 * hitmeister-config: 
 *  - hitmeister.lang isset
 *  - amzon.prepare.ean !=''
 */
class ML_Shopware6PriceMinister_Model_ProductList_PriceMinister_Prepare_Apply extends ML_Shopware6_Model_ProductList_Abstract {

    protected function executeFilter() {
        $this->oFilter
            ->registerDependency('searchfilter')
            ->limit()
            ->registerDependency('categoryfilter')
            ->registerDependency('preparestatusfilter')
            ->registerDependency('priceministerpreparetypefilter',array('PrepareType'=>'apply'))
            ->registerDependency('productstatusfilter')
            ->registerDependency('manufacturerfilter')
        ;
        return $this;
    }

    public function getSelectionName() {
        return 'apply';
    }

    /**
     * Filters prepared attributes matching data additionally before comparing it to global matching.
     *
     * @param array $preparedData Attributes matching data from Prepare table for product
     * @param string $categoryCode Category code
     */
    protected function filterAMPreparedDataBeforeComparison(&$preparedData, $categoryCode)
    {
        $response = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryAttributes',
                'DATA' => array('CategoryID' => $categoryCode))
        );

        $aCategoryAttributes = !empty($response['DATA']) ? $response['DATA'] : array();

        foreach ($preparedData as $attributeKey => $attribute) {
            if (in_array($attributeKey, $aCategoryAttributes)) {
                unset($preparedData[$attributeKey]);
            }
        }
    }
}
