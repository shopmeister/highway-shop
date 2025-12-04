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
if (!class_exists('ML', false))
    throw new Exception();

$localClientBuild = MLSetting::gi()->get('sClientBuild');
$localClientBuild = empty($localClientBuild) ? $this->__('ML_LABEL_UNKNOWN') : $localClientBuild;
$currentClientBuild = MLSetting::gi()->get('sCurrentBuild');
$currentClientBuild = empty($currentClientBuild) ? $this->__('ML_LABEL_UNKNOWN') : $currentClientBuild;

?>

<div class="bold">
    <?php $currentVersion = MLShop::gi()->getPluginVersion();
    $aLatest = MLShop::gi()->getPluginLatestVersion();

    ?>
    <span class="version-text">magnalister Shopware Version</span>
    <span class="version"><?php echo $currentVersion; ?></span>
    <?php if (isset($aLatest['PluginVersion']) && version_compare($aLatest['PluginVersion'], $currentVersion, '>')) { ?>
        <a href="/" class="latest-version-text">( <?php echo MLI18n::gi()->get('NewVersionIsAvailable', array('version' => $aLatest['PluginVersion'])) ?>
            )</a>
    <?php } ?>
</div>

<script type="text/javascript">/*<![CDATA[*/
    (function ($) {
        $(document).ready(function () {

            $("#magnafooter").on('click', "a.latest-version-text", function (e) {
                e.preventDefault();
                var url = "";
                var pathArray = window.location.pathname.split('/');
                for (const index in pathArray) {
                    if (pathArray[index] === "") {
                        continue;
                    }
                    if (pathArray[index] === "magnalister") {
                        break;
                    }
                    url += "/" + pathArray[index];
                }
                <?php if(version_compare(MLSHOPWAREVERSION, '6.4.0.0', '>=')){?>
                url += "/admin#/sw/extension/my-extensions/listing/app";
                <?php }else{ ?>
                url += "/admin#/sw/plugin/index/updates"
                <?php } ?>
                window.open(url, '_blank').focus();
                return false;
            });
        });
    })(jqml);
    /*]]>*/</script>