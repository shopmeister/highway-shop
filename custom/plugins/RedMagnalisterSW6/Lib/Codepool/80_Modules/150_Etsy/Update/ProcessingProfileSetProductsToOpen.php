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

MLFilesystem::gi()->loadClass('Core_Update_Abstract');

class ML_Etsy_Update_ProcessingProfileSetProductsToOpen extends ML_Core_Update_Abstract {
    const CONFIG_KEY = 'EtsyProcessingProfileSetProductsToOpen';
    public function needExecution() {
        // Has migration already been executed? If yes, skip.
        $aConfig = MLDatabase::factorySelectClass()->select('`value`')
            ->from('magnalister_config')
            ->where("mpid = '0' AND mkey = '".self::CONFIG_KEY."'")
            ->getResult();
        if (isset($aConfig[0]['value'])) {
            return false;
        }
        if(//the user could use Etsy before but now not anymore, so table stay like before without ProcessingProfile
            MLDatabase::getDbInstance()->tableExists('magnalister_etsy_prepare') === false
            || MLDatabase::getDbInstance()->columnExistsInTable('ProcessingProfile', 'magnalister_etsy_prepare') === false
        ){
            return false;
        }
        // Run only if there are rows that match the criteria
        $aRows = MLDatabase::factorySelectClass()->select('COUNT(1) AS cnt')
            ->from('magnalister_etsy_prepare')
            ->where("Verified = 'OK' AND (ProcessingProfile = '' OR ProcessingProfile IS NULL)")
            ->getResult();

        return (isset($aRows[0]['cnt']) && (int)$aRows[0]['cnt'] > 0);
    }

    public function execute() {
        try {
            $db = MLDatabase::getDbInstance();
            if ($db->tableExists('magnalister_etsy_prepare')) {
                $batchSize = 200;
                $totalUpdated = 0;
                while (true) {
                    // Fetch up to 200 keys to update
                    $rows = $db->fetchArray(
                        "SELECT mpID, products_id                        
                        FROM magnalister_etsy_prepare                        
                        WHERE Verified = 'OK' AND (ProcessingProfile = '' OR ProcessingProfile IS NULL)                        
                        LIMIT " . $batchSize,
                        true
                    );

                    if (empty($rows)) {
                        break; // nothing left to update
                    }

                    // Build WHERE clause for this batch
                    $conditions = array();
                    foreach ($rows as $row) {
                        $mpID = (int)$row['mpID'];
                        $pid = (int)$row['products_id'];
                        $conditions[] = "(mpID = {$mpID} AND products_id = {$pid})";
                    }
                    $where = implode(' OR ', $conditions);

                    // Update only the current batch
                    $db->query("UPDATE magnalister_etsy_prepare SET Verified = 'OPEN' WHERE " . $where);

                    $totalUpdated += count($rows);
                }

                // Mark migration as done if script executed without errors
                MLDatabase::getDbInstance()->insert('magnalister_config', array('mpid' => 0, 'mkey' => self::CONFIG_KEY, 'value' => 1));

                MLMessage::gi()->addDebug('Etsy ProcessingProfile migration updated rows', $totalUpdated);
            }
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
        }
        return parent::execute();
    }

}
