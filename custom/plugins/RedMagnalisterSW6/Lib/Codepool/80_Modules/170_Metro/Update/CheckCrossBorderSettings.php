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

MLFilesystem::gi()->loadClass('Core_Update_Abstract');

/**
 * Check if cross border settings need to be corrected.
 */
class ML_Metro_Update_CheckCrossBorderSettings extends ML_Core_Update_Abstract {
    /**
     * Execute the logic.
     *
     * @return self
     * @throws Exception
     */
    public function execute() {
        // we need a manual require here, MLFilesystem or MLHelper doesn't work
        require_once __DIR__.'/../Helper/Model/CrossBordersConfiguration.php';
        $crossBordersConf = new ML_Metro_Helper_Model_CrossBordersConfiguration();
        $crossBordersConf->fixConfigurationForMarketplaceWithSameOrigin($crossBordersConf);
        return parent::execute();
    }

}
