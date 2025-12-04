<?php 
    /* @var $this  ML_Productlist_Controller_Widget_ProductList_Abstract */
    /* @var $oList ML_Productlist_Model_ProductList_Abstract */
    /* @var $oProduct ML_Shop_Model_Product_Abstract */
     if (!class_exists('ML', false))
         throw new Exception();
if ($this instanceof ML_Productlist_Controller_Widget_ProductList_Abstract) {
    if ($this->renderVariantsError() || $this->renderVariants()) {
        $aVariants = $oList->getVariants($oProduct);
    }
    if ($this->renderVariantsError() && $this instanceof ML_Productlist_Controller_Widget_ProductList_Selection) {
        $iCountVariants = 0;
        $aMessages = array();
        foreach ($aVariants as $oVariant) {
            $iCountVariants += (int)$this->productSelectable($oVariant, false);
            foreach (MLMessage::gi()->getObjectMessages($oVariant) as $sMessage) {
                    $aMessages[$sMessage]=isset($aMessages[$sMessage])?$aMessages[$sMessage]+1:1;
                }
            }
            if(!empty($aMessages)){
                $sAddMessage='<ul>';
                foreach($aMessages as $sMessage=>$iCount){
                    $sAddMessage.='<li>'.$iCount.'&nbsp;*&nbsp;'.$sMessage.'</li>';
                }
                $sAddMessage.='</ul>';
                MLMessage::gi()->addObjectMessage($oProduct, MLI18n::gi()->get('Productlist_ProductMessage_sVariantsHaveError').$sAddMessage);
            }
        }
        $this->includeView('widget_productlist_list_mainarticle',array('oList'=>$oList,'oProduct'=>$oProduct));
        if($this->renderVariants()){
            if ($oProduct->exists() && count($aVariants) > 0 && count(MLMessage::gi()->getObjectMessages($oProduct))==0) { 
                $oCurrentProduct = $oProduct;
                foreach ($aVariants as $oProduct) {
                    ?>
                        <tr class="child">
                            <td colspan="2"></td>
                            <td class="next-child" colspan="<?php echo count($oList->getHead())+($this instanceof ML_Productlist_Controller_Widget_ProductList_Selection?1:0)-1; ?>">
                                <div class="title">
                                    <?php echo $oProduct->getName(); ?>&nbsp;&nbsp;
                                    <span>
                                        [<span style="color: #000;"><?php echo MLI18n::gi()->get('ML_LABEL_ARTICLE_NUMBER'); ?></span>: <?php echo $oProduct->getSku(); ?>,
                                        <span style="color: #000;"><?php echo MLI18n::gi()->get('ML_LABEL_SHOP_PRICE_BRUTTO'); ?></span>: <?php echo $oProduct->getShopPrice(true, true); ?>]
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php
                    if(!$oProduct->isSingle()){
                        $this->includeView('widget_productlist_list_variantarticle',array('oList'=>$oList,'oProduct'=>$oProduct)); 
                    }
                    foreach ($oList->additionalRows($oProduct) as $sAddRow) {
                        $this->includeView('widget_productlist_list_variantarticleadditional_'.$sAddRow, array('oProduct'=>$oProduct,'aAdditional'=>isset($aAdditional)?$aAdditional:array()));
                    }
                }
            }
        }
    }
?>