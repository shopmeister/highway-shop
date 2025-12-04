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

class ML_Amazon_Update_ListingApiSetProductPrepareStatusToMigration extends ML_Core_Update_Abstract {

    public function needExecution() {
        $aConfig = MLDatabase::factorySelectClass()->select('`value`')
            ->from('magnalister_config')
            ->where("mpid = '0' AND mkey = 'AmazonListingApiMigration'")
            ->getResult();

        return !isset($aConfig[0]['value']);
    }

    public function execute() {
        try {
            if (MLDatabase::getDbInstance()->tableExists('magnalister_amazon_prepare')) {
                // Check if column exists and has the migration enum value
                $currentType = MLDatabase::getDbInstance()->columnType('IsComplete', 'magnalister_amazon_prepare');
                
                if ($currentType) {
                    // Check if "migration" is already in the enum values
                    if (stripos($currentType, "'migration'") === false) {
                        // Add "migration" to the enum values
                        // Extract current enum values and add migration
                        if (preg_match('/enum\((.*)\)/', $currentType, $matches)) {
                            $values = $matches[1];
                            // If values end with single quote, add comma and migration, otherwise just add migration
                            if (substr(trim($values), -1) === "'") {
                                $newType = str_replace(')', ",'migration')", $currentType);
                            } else {
                                $newType = str_replace(')', "'migration')", $currentType);
                            }
                            
                            // Alter the column to add the new enum value
                            MLDatabase::getDbInstance()->query("ALTER TABLE magnalister_amazon_prepare MODIFY COLUMN IsComplete " . $newType);
                        }
                    }
                }

                // Update records as originally intended
                MLDatabase::getDbInstance()->update('magnalister_amazon_prepare',
                    [
                        'IsComplete' => 'false',
                    ],
                    [
                        'PrepareType' => 'apply'
                    ]
                );
                MLDatabase::getDbInstance()->insert('magnalister_config', array('mpid' => 0, 'mkey' => 'AmazonListingApiMigration', 'value' => 1));
            }
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
        }
        return parent::execute();
    }

}
