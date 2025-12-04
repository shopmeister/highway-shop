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
 * Deletes old controller files for shop systems which don't create an empty directory for an update.
 *
 * Shopware 5 and PrestaShop for example.
 */
class ML_Metro_Update_MetroRemoveOldFiles extends ML_Core_Update_Abstract {
    /**
     * Execute the logic.
     *
     * @return self
     * @throws Exception
     */
    public function execute() {
        $aRemoveOldFilesList = array(
            '20_Prepare.php',
            '30_PriceAndStock.php',
            '40_Order.php',
            '45_Invoice.php',
        );

        $baseDir = __DIR__.DIRECTORY_SEPARATOR.'..'.
            DIRECTORY_SEPARATOR.'Controller'.
            DIRECTORY_SEPARATOR.'Metro'.
            DIRECTORY_SEPARATOR.'Config'.
            DIRECTORY_SEPARATOR;

        foreach ($aRemoveOldFilesList as $file) {
            if (file_exists($baseDir.$file)) {
                unlink($baseDir.$file);
            }
        }

        return parent::execute();
    }
}
