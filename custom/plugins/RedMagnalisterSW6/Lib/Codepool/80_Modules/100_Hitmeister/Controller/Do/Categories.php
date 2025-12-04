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

MLFilesystem::gi()->loadClass('Modul_Controller_Do_Categories');

class ML_Hitmeister_Controller_Do_Categories extends ML_Modul_Controller_Do_Categories {

    /**
     * Override parent method to remove TRUNCATE logic.
     * Categories are managed by Expires field and site-specific data.
     */
    public function callAjaxGetChildCategories() {
        $sType = MLRequest::gi()->get('type');
        // Don't truncate the table - we have site-specific categories with Expires management
        $this->includeView('do_categories_childcategories', array('sParentId' => MLRequest::gi()->get('parentid'), 'sType' => $sType));
    }
}