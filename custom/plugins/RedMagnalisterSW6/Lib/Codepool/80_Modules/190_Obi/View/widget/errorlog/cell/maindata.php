<?php
/* @var $oErrorlog ML_ErrorLog_Model_Table_ErrorLog */
/* @var $oProduct ML_Shop_Model_Product_Abstract */
 if (!class_exists('ML', false))
     throw new Exception();

$aData = $oErrorlog->get('data');
?>
<?php if (isset($oProduct)) { ?>
    <table>
        <tr>
            <td>SKU</td>
            <td><?php echo $oProduct->getSku() ?></td>
        </tr>
        <tr>
            <td><?php echo MLI18n::gi()->ML_LABEL_SHOP_TITLE; ?></td>
            <td class="product-link">
                <a title="<?php echo MLI18n::gi()->ML_LABEL_EDIT ?>" target="_blank" href="<?php echo $oProduct->getEditLink() ?>">
                    <?php echo $oProduct->getName() ?></a></td>
        </tr>
        <?php if (isset($aData) && is_array($aData) && isset($aData['Field'])) { ?>
            <tr>
                <td><?php echo $this->__('ML_OBI_DATAFIELD') ?></td>
                <td><?php echo $aData['Field']; ?></td>
            </tr>
        <?php } ?>
    </table>

    <?php
} else {
    ?>
    <table>
        <tr><?php
            if (!empty($aData['ObiOrderId'])) {
                $oOrder = MLOrder::factory()->getByMagnaOrderId($aData['ObiOrderId']);
                if ($oOrder->get('orders_id') !== null) {
                    ?>
                    <td><?php echo $this->__('ML_OBI_ORDER_ID') ?></td>
                    <td class="order-link">
                        <a title="<?php echo $this->__('ML_LABEL_EDIT') ?>" target="_blank" class="ml-js-noBlockUi" href="<?php echo $oOrder->getEditLink() ?>"><?php echo $aData['ObiOrderId'] ?></a>
                    </td><?php
                } else {
                    ?>
                    <td>ObiOrderId</td>
                    <td><?php echo $aData['ObiOrderId']; ?></td>
                    <?php
                }
            } else {
                if (is_array($aData)) {
                    if (isset($aData['SKU'])) {
                        $sLabel = 'SKU';
                        $sValue = $aData['SKU'];
                    } else {
                        $sLabel = key($aData);
                        $sValue = current($aData);
                    }
                } else {
                    $sLabel = '';
                    $sValue = $aData;
                }
                ?>
                <td><?php echo $sLabel; ?></td>
                <td><?php echo $sValue; ?></td>
                <?php
            }
            ?>
        </tr>
        <?php if (isset($aData) && is_array($aData) && isset($aData['ObiOrderId'])) { ?>
            <tr>
                <td><?php echo $this->__('ML_OBI_ORDER_NUMBER') ?></td>
                <td><?php echo $aData['ObiOrderId']; ?></td>
            </tr>
        <?php } ?>
        <?php if (isset($aData) && is_array($aData) && isset($aData['Field'])) { ?>
            <tr>
                <td><?php echo $this->__('ML_OBI_DATAFIELD') ?></td>
                <td><?php echo $aData['Field']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php
}