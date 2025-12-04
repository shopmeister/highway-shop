<?php
if(MLSetting::gi()->blDebug) {
    $aButtons = MLSetting::gi()->get('aButtons');
    $aMyButtons = array();
    foreach ($aButtons as $aButton) {
        $aMyButtons[] = $aButton;
        //To add 'SyncProductIdentifiers' and 'CacheAPICalls' button after 'SyncInventory' button
        if ($aButton['link'] == array('do' => 'SyncInventory')) {
            $aMyButtons[] = array(
                'title' => 'sEbayProductIdentifierSyncButton',
                'warningTitle' => 'ML_MESSAGE_BEFORE_SYNC_ProductIdentifier_TITLE',
                'warningText' => 'ML_MESSAGE_BEFORE_SYNC_ProductIdentifier_TEXT',
                'icon' => 'sync',
                'link' => array('do' => 'SyncProductIdentifiers'),
                'type' => 'cron',
                'id' => 'cron_sync_product_identifiers',
                'enabled' => MLShop::gi()->addonBooked('EbayProductIdentifierSync'),
                'disablemessage' => MLI18n::gi()->get('sEbaySyncButtonDisableIfno'),
                'mpFilter' => 'ebay' // only ebay
            );
            //Adding CacheAPICalls buttons on eBay marketplace and active it in the "service developers -> do" list
            if (MLSetting::gi()->get('blDebug')) {
                $aMyButtons[] = array(
                    'title' => 'sEbayCacheAPICalls',
                    'warningTitle' => 'ML_MESSAGE_BEFORE_SYNC_CacheAPICalls_TITLE',
                    'warningText' => 'ML_MESSAGE_BEFORE_SYNC_CacheAPICalls_TEXT',
                    'icon' => 'ml-marketplaces-api-cache-refresh-button',
                    'link' => array('do' => 'CacheAPICalls'),
                    'type' => 'cron',
                    'id' => 'cron_cache_api_calls',
                    'enabled' => true,
                    'mpFilter' => 'ebay' // only ebay
                );
            }
        }
    }
    MLSetting::gi()->set('aButtons', $aMyButtons, true);
}