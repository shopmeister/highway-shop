<?php
if(MLSetting::gi()->blDebug) {
    $aButtons = MLSetting::gi()->get('aButtons');
    $aMyButtons = array();
    foreach ($aButtons as $aButton) {
        $aMyButtons[] = $aButton;
        //To add 'CacheAPICalls' button after 'SyncInventory' button
        if ($aButton['link'] == array('do' => 'SyncInventory')) {
            //Adding CacheAPICalls buttons on Amazon marketplace and active it in the "service developers -> do" list
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
                    'mpFilter' => 'amazon' // only ebay
                );
            }
        }
    }
    MLSetting::gi()->set('aButtons', $aMyButtons, true);
}