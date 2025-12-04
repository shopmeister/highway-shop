<?php if (!class_exists('ML', false))
    throw new Exception(); ?>
<div class="shipping">
    <?php
    $aField = array('name' => substr($aField['realname'], 0, -9));
    $aField = $this->getField($aField);
    $this->includeType($aField);
    if (!MLHttp::gi()->isAjax() || MLHelper::gi('model_form_type_sellerprofiles')->hasSellerProfiles()) { // @todo check if neccessary else sellerprofile: all fields via ajax
        ?>
        <div class="profile">
            <div>
                <?php
                    $aProfile=array('name'=>$aField['realname'].'profile');
                    $aProfile=$this->getField($aProfile);
                    $this->includeType($aProfile);
                    $aDiscount=array('name'=>$aField['realname'].'discount');
                    $aDiscount=$this->getField($aDiscount);
                    $aDiscount['i18n']['valuehint'] = $aDiscount['i18n']['label'];
                    $this->includeType($aDiscount);
                ?>
            </div>
        </div>
    <?php
}
?>
</div>