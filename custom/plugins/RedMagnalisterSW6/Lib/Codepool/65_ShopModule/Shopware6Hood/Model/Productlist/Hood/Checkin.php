<?php

MLFilesystem::gi()->loadClass('Shopware6_Model_ProductList_UploadAbstract');
class ML_Shopware6Hood_Model_ProductList_Hood_Checkin extends ML_Shopware6_Model_ProductList_UploadAbstract {
    
    protected function executeFilter(){
         $this->oFilter
            ->registerDependency('searchfilter')
            ->limit()
            ->registerDependency('selectionstatusfilter', array('selectionname' => $this->getSelectionName()))
            ->registerDependency('categoryfilter')
            ->registerDependency('preparestatusfilter', array('blPrepareMode' => false))
            ->registerDependency('lastpreparedfilter', array('blPrepareMode' => false))
            ->registerDependency('marketplacesyncfilter', array('blPrepareMode' => false))
            ->registerDependency('productstatusfilter', array('blPrepareMode' => false))
            ->registerDependency('manufacturerfilter')
        ;
        return $this;
    }

}