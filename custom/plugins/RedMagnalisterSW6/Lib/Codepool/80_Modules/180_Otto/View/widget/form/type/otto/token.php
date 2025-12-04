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

if (!class_exists('ML', false)) {
    throw new Exception();
}
/**
 * @var array $aField
 */

/** @var ML_Otto_Model_Modul $module */
$module = MLModule::gi();

$isAuthed = false;
$appUpgradeNeeded = false;
try {
    $isAuthed = $module->isAuthed();
    $appUpgradeNeeded = $isAuthed && ($module->getCurrentAppVersion() !== $module->getLastestAppVersion());
} catch (Exception $e) {
    // Not authed yet, ignore
}
?>

<input class="mlbtn action text<?php
    echo (($isAuthed && ($module->getCurrentAppVersion() === $module->getLastestAppVersion())) ? ' disabled': '');
    ?>" type="button" value="<?php echo (($appUpgradeNeeded) ? MLI18n::gi()->get('ML_OTTO_BUTTON_TOKEN_APP_UPGRADE') : MLI18n::gi()->get('ML_OTTO_BUTTON_TOKEN_NEW')); ?>" id="requestToken<?php echo $aField['id'] ?>"/>
<span>App-Version: <?php echo $module->getCurrentAppVersion(); ?></span>
    <script type="text/javascript">/*<![CDATA[*/
    jqml(document).ready(function () {
        jqml('#requestToken<?php echo $aField['id']?>').click(function (e) {
            if (jqml(this).hasClass('disabled')) {
                e.preventDefault();
                return;
            }
            jqml.blockUI(blockUILoading);
            jqml.ajax({
                'method': 'get',
                'url': '<?php echo MLHttp::gi()->getCurrentUrl(array('method' => 'GetTokenCreationLink', 'what' => $aField['realname'], 'kind' => 'ajax')) ?>',
                'success': function (data) {
                    jqml.unblockUI();
                    let error = false;
                    try {
                        data = jqml.parseJSON(data);
                        console.log(data);
                    } catch (e) {
                        console.log(e);
                        error = true;
                    }
                    if (error || data.error + '' !== '') {
                        jqml('<div></div>')
                            .attr('title', '<?php echo $this->__s('ML_ERROR_CREATE_TOKEN_LINK_HEADLINE', array('\'', "\n", "\r")) ?>')
                            .html('<?php echo $this->__s('ML_ERROR_CREATE_TOKEN_LINK_TEXT', array('\'', "\n", "\r"));  ?>')
                            .jDialog();
                    } else {
                        let hwin = window.open(data.iframeUrl, "popup", "resizable=yes,scrollbars=yes");
                        if (hwin.focus) {
                            hwin.focus();
                        }
                    }
                }
            });
        });
    });
    /*]]>*/</script>
