<?php
MLFilesystem::gi()->loadClass('Shopware6_Model_ProductList_Abstract');
class ML_Shopware6_Model_ProductList_All extends ML_Shopware6_Model_ProductList_Abstract{
    protected function executeFilter() {
        return $this;
    }

    protected function executeList() {
        
    }

    public function getSelectionName() {
        
    }

}