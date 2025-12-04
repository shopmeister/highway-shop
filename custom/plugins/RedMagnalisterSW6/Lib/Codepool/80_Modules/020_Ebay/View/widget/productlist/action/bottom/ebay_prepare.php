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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
    /* @var $this  ML_Productlist_Controller_Widget_ProductList_Abstract */
    /* @var $oList ML_Productlist_Model_ProductList_Abstract */
    /* @var $aStatistic array */
     if (!class_exists('ML', false))
         throw new Exception();
?>
<?php if ($this instanceof ML_Productlist_Controller_Widget_ProductList_Abstract) {
    $this->includeView('widget_productlist_action_resetButton');
} ?>

<?php

if ((int)(MLDatabase::factory('ebay_categories')->getList()->getQueryObject()->getCount()) === 0) { ?>

    <a style="visibility: hidden"
       class="ui-icon ui-corner-all ui-state-focus global-ajax ui-icon-arrowrefresh-wrap ml-js-noBlockUi"
       id="importCategories" title="<?php echo MLI18n::gi()->get('ML_EBAY_IMPORT_CATEGORIES') ?>"
       href="<?php echo MLHttp::gi()->getUrl(array('mpid' => MLModule::gi()->getMarketPlaceId(), 'do' => 'ImportCategories')); ?>">
        <span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>
    </a>
    <script>
        jqml(document).ready(function () {
            var currentA = jqml('#importCategories');
            currentA.magnalisterRecursiveAjax({
                sOffset: '<?php echo MLHttp::gi()->parseFormFieldName('offset') ?>',
                sAddParam: '<?php echo MLHttp::gi()->parseFormFieldName('ajax') ?>=true',
                oI18n: {
                    sProcess: '<?php echo $this->__s('ML_STATUS_FILTER_SYNC_CONTENT', array('\'')) ?>',
                    sError: '<?php echo $this->__s('ML_ERROR_LABEL', array('\'')) ?>',
                    sSuccess: '<?php echo $this->__s('ML_OTTO_IMPORT_CATEGORIES_SUCCESS', array('\'')) ?>'
                },
                onFinalize: function () {
                    window.location = window.location;//reload without post
                },
                onProgessBarClick: function (data) {
                    console.dir({data: data});
                },
                blDebug: <?php echo MLSetting::gi()->get('blDebug') ? 'true' : 'false' ?>,
                sDebugLoopParam: "<?php echo MLHttp::gi()->parseFormFieldName('saveSelection') ?>=true"
            });
        });
    </script>
<?php } ?>
