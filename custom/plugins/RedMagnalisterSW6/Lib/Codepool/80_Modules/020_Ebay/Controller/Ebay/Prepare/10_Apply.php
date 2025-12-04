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

MLFilesystem::gi()->loadClass('Ebay_Controller_Widget_ProductList_Ebay_Abstract');

class ML_Ebay_Controller_Ebay_Prepare_Apply extends ML_Ebay_Controller_Widget_ProductList_Ebay_Abstract {
    protected $aPreparationResetFields = array(
        'reset_title' => 'Title',
        'reset_subtitle' => 'Subtitle',
        'reset_description' => 'Description',
        'reset_pictures' => array('PictureURL', 'VariationPictures'),
        'reset_attributes' => 'ShopVariation',
        'reset_strikeprices' => 'StrikePrice',
    );
    protected $aPreparationResetFieldsValues = array(
        'StrikePrice' => 'false'
    );
    public static function getTabTitle() {
        return MLI18n::gi()->get('ebay_prepare_apply');
    }

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return $this|ML_Core_Controller_Abstract
     * @throws Exception
     */
    public function getProductListWidget() {
        return ML_Productlist_Controller_Widget_ProductList_Selection::getProductListWidget();
    }

    /**
     * @throws Exception dont need in this view, shows only prepared value
     */
    public function getPriceObject(ML_Shop_Model_Product_Abstract $oProduct) {
        throw new Exception('price config can not loaded yet.');
    }

}
