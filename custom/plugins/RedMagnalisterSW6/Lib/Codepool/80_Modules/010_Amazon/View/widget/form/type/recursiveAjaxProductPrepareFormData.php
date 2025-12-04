<?php

use PhpParser\Internal\DiffElem;
/**
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
 * $Id$
 *
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
if (!class_exists('ML', false))
    throw new Exception();
?>
<?php if (
        substr(strtolower($this->getIdent()), -strlen('_prepare_apply_form')) === '_prepare_apply_form'
        || substr(strtolower($this->getIdent()), -strlen('_prepare_form')) === '_prepare_form'
) {

    ?>
    <div style="display: none" id="magnalister_recursive_ajax_data"
         data-sprocess="<?php echo $this->__s('ML_STATUS_FILTER_SYNC_CONTENT',array('\'')); ?>"
         data-stitle="<?php echo $this->__s('form_product_preparation_title',array('\'')); ?>"
         data-stitle-variation="<?php echo $this->__s('form_variation_theme_title',array('\'')); ?>"
         data-stitle-attribute="<?php echo $this->__s('amazon_attribute_matching_title',array('\'')); ?>"
         data-serror="<?php echo $this->__s('ML_ERROR_LABEL',array('\'')); ?>"
         data-ssuccess="<?php echo $this->__s('ML_LABEL_SAVED_SUCCESSFULLY',array('\'')); ?>"
         data-bldebug="<?php echo MLSetting::gi()->get('blDebug') ? 'true' : 'false'; ?>"
         data-offset="<?php echo MLHttp::gi()->parseFormFieldName('offset'); ?>"
         data-ajax="<?php echo MLHttp::gi()->parseFormFieldName('ajax'); ?>"
         data-saveselection="<?php echo MLHttp::gi()->parseFormFieldName('saveSelection'); ?>"
         data-redirect="<?php echo $this->getParentUrl(); ?>">
    </div>
<?php } ?>


