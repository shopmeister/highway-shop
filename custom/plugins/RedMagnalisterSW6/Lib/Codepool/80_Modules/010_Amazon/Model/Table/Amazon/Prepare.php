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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Database_Model_Table_Prepare_Abstract');

class ML_Amazon_Model_Table_Amazon_Prepare extends ML_Database_Model_Table_Prepare_Abstract {

    protected $sTableName = 'magnalister_amazon_prepare';

    protected $aFields = array(
        'mpID'                     => array(
            'isKey' => true,
            'Type'  => 'int(8) unsigned', 'Null' => self::IS_NULLABLE_NO, 'Default' => NULL, 'Extra' => '', 'Comment' => 'marketplaceid'
        ),
        'ProductsID'               => array(
            'isKey' => true,
            'Type'  => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => NULL, 'Extra' => '', 'Comment' => 'magnalister_products.id'
        ),
        'PreparedTS'               => array(
            'isInsertCurrentTime' => true,
            'Type'                => 'datetime', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'PrepareType'              => array(
            'Type' => "enum('manual','auto','apply')", 'Null' => self::IS_NULLABLE_NO, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'AIdentID'                 => array(
            'Type' => 'varchar(16)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'AIdentType'               => array(
            'Type' => "enum('ASIN','EAN','UPC')", 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        // @deprecated price comes only from mp-config dont need saving
        'Price'                    => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'LeadtimeToShip'           => array(
            /** @deprecated now shippingtime */
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'ShippingTime'             => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'Quantity'                 => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        ),
        'LowestPrice'              => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_YES, 'Default' => null, 'Extra' => '', 'Comment' => 'lowest price (amazon)'
        ),
        'ConditionType'            => array(
            'Type' => 'varchar(50)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'item condition'
        ),
        'ConditionNote'            => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'additional condition info'
        ),
        'Shipping'                 => array(
            'Type' => 'varchar(10)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'old will ship internationally'
        ),
        'MainCategory'             => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply'
        ),
        'ProductType'              => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply'
        ),
        'BrowseNodes'              => array(
            'Type'=> 'text',      'Null'=> self::IS_NULLABLE_NO,     'Default' => '' ,'Extra'   => '', 'Comment' => 'only apply'
        ),
        'Attributes'               => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'ShopVariation'            => array(
            'Type' => 'longtext', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply (legacy - contains JSON)'
        ),
        'ShopVariationId'          => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'TextId reference to magnalister_amazon_prepare_longtext'
        ),
        'variation_theme'          => array(
            'Type' => 'varchar(500)', 'Null' => self::IS_NULLABLE_YES, 'Default' => '{"autodetect":[]}', 'Extra' => '', 'Comment' => ''
        ),
        'ItemTitle'                => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'Manufacturer'             => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'Brand'                    => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'ManufacturerPartNumber'   => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'EAN'                      => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'Images'                   => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'BulletPoints'             => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'Description'              => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'Keywords'                 => array(
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'TopMainCategory'          => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply, for top-ten-categories'
        ),
        'TopProductType'           => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply, for top-ten-categories'
        ),
        'TopBrowseNode1'           => array(
            'Type' => 'varchar(255)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply, for top-ten-categories'
        ),
        'TopBrowseNode2'           => array(
            'Type' => 'varchar(255)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '', 'Extra' => '', 'Comment' => 'only apply, for top-ten-categories'
        ),
        'ApplyData'                => array(
            /** @deprecated */
            'Type' => 'text', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'only apply'
        ),
        'B2BActive'                => array(
            'Type' => "enum('true','false')", 'Null' => self::IS_NULLABLE_NO, 'Default' => 'false', 'Extra' => '', 'Comment' => ''
        ),
        'B2BSellTo'                => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 'b2b_b2c', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountType'          => array(
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 'no', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier1Quantity' => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 0, 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier2Quantity' => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 0, 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier3Quantity' => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 0, 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier4Quantity' => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 0, 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier5Quantity' => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_NO, 'Default' => 0, 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier1Discount' => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '0.00', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier2Discount' => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '0.00', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier3Discount' => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '0.00', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier4Discount' => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '0.00', 'Extra' => '', 'Comment' => ''
        ),
        'B2BDiscountTier5Discount' => array(
            'Type' => 'decimal(15,2)', 'Null' => self::IS_NULLABLE_NO, 'Default' => '0.00', 'Extra' => '', 'Comment' => ''
        ),
        'IsComplete'               => array(
            'Type' => "enum('true','false','migration')", 'Null' => self::IS_NULLABLE_NO, 'Default' => 'false', 'Extra' => '', 'Comment' => 'if matching, true'
        ),
        'Verified'                 => array(
            'Type' => "enum('OK','ERROR','OPEN','EMPTY')", 'Null' => self::IS_NULLABLE_NO, 'Default' => 'OPEN', 'Extra' => '', 'Comment' => ''
        ),
        'ShippingTemplate'         => array(
            'Type' => 'int(11)', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => ''
        )
    );

    protected $aTableKeys = array(
        'UC_products_id'               => array('Non_unique' => '0', 'Column_name' => 'mpID, ProductsID'),
        'UC_shopvariation_id'               => array('Non_unique' => '1', 'Column_name' => 'ShopVariationId'),
    );

    protected function setDefaultValues() {
        try {
            $sId = MLRequest::gi()->get('mp');
            if (is_numeric($sId)) {
                $this->set('mpid', $sId);
            }
        } catch (Exception $oEx) {
            
        }
        return $this;
    }

    /**
     * get productid by asin or ean
     *
     * @param $sIdentValue
     * @param $sIdentType
     * @param null $iMpId
     * @return mixed
     */
    public function getByIdentifier($sIdentValue , $sIdentType , $iMpId = null) {
         $this->aKeys = array ('mpid' , 'aidenttype' , 'aidentid') ;
         if ( $iMpId === null ) {
             $iMpId = MLModule::gi()->getMarketplaceId();
         }
         $this->set('aidenttype' , $sIdentType)
                 ->set('aidentid' , $sIdentValue);
         return $this->get('productsid') ;
     }
     
    public function getVariantCount($mProduct) {
        $iMasterProductId = (int)(
            $mProduct instanceof ML_Database_Model_Table_Abstract
            ? $mProduct->get('id') 
            : $mProduct
        );
        $sSql = "
            SELECT COUNT(*) 
            FROM magnalister_products p
            INNER JOIN magnalister_amazon_prepare s ON p.id = s.productsid
            WHERE     p.parentid = '".$iMasterProductId."'
                  AND s.mpid = '".MLRequest::gi()->get('mp')."'
                  AND s.iscomplete = 'true'
        ";
        return MLDatabase::getDbInstance()->fetchOne($sSql);
    }
    
    public function resetTopTen($sType , $sValue){
        $oQuery = $this->getList()->getQueryObject();
        $oQuery->update($this->sTableName, array($sType=>''))->where("$sType = '$sValue'")->doUpdate();        
    }


    /**
     * field name for join magnalister_product.id
     * @return string
     */
    public function getProductIdFieldName() {
        return 'productsid';
    }
    
    /**
     * field name for prepared-status
     * @return string
     */
    public function getPreparedStatusFieldName() {
        return 'isComplete';
    }
    
    /**
     * field value for successfully prepared item of $this->getPreparedFieldName()
     * @return string
     */
    public function getIsPreparedValue() {
        return 'true';
    }
    
    /**
     * field name for prepared-type if exists
     * @return string|null
     */
    public function getPreparedTypeFieldName () {
        return 'PrepareType';
    }

    public function isVariationMatchingSupported() {
        return true;
    }

    public function getPrimaryCategoryFieldName() {
        return 'MainCategory';
    }

    /**
     * Fields that should be stored in PrepareLongText table
     *
     * Format: 'legacy_field_name' => 'id_field_name'
     * Example: 'shopvariation' => 'shopvariationid'
     *
     * @var array
     */
    protected $aLongTextFields = array(
        'shopvariation' => 'shopvariationid'
    );

    /**
     * Cache TextId values loaded from database
     * Used to preserve TextId when form doesn't send shopvariation data during batch submit
     *
     * @var array
     */
    protected $aLoadedTextIds = array();

    /**
     * Flag to prevent load() from reloading long text fields during save()
     * When true, load() will skip fetching from PrepareLongText table
     *
     * @var bool
     */
    protected $skipLoadingLongText = false;

    /**
     * Override load() to fetch long text fields from PrepareLongText table
     *
     * New Transparent Approach:
     * 1. Load main record via parent::load()
     * 2. For each long text field (e.g., ShopVariation):
     *    - Check if *Id field (e.g., ShopVariationId) has a TextId
     *    - If yes: Fetch JSON string from PrepareLongText table
     *    - Store JSON string (NOT decoded array) in aData['shopvariation']
     * 3. parent::get() will auto-decode JSON via MLHelper::getEncoderInstance()->decode()
     *
     * CRITICAL: Why JSON string, not decoded array?
     * - parent::get() calls MLHelper::getEncoderInstance()->decode($this->aData[$sName])
     * - Encoder->decode() expects STRING, not ARRAY
     * - If we store decoded array in aData, get() will call json_decode(ARRAY) → TypeError!
     * - So we must store JSON string, let parent::get() handle decoding
     *
     * Benefits:
     * - Transparent: get()/set() work normally
     * - No magic: Standard parent behavior
     * - Performance: Only one additional query per load
     * - Simple: No complex caching logic
     *
     * Backward Compatibility:
     * - If ShopVariationId is NULL, check legacy ShopVariation field
     * - If ShopVariation contains JSON string, use it (old data)
     * - No migration needed - works with both old and new data
     *
     * @return $this
     */
    public function load() {
        $return = parent::load();

        // After loading main record, fetch long text fields
        foreach ($this->aLongTextFields as $sFieldName => $sIdFieldName) {
            $sNameLower = strtolower($sFieldName);

            // NEW APPROACH: Check if *Id field has TextId
            $sTextId = isset($this->aData[$sIdFieldName]) ? $this->aData[$sIdFieldName] : null;

            // IMPORTANT: Cache loaded TextId for later use in save()
            // This protects against form submit clearing TextId when shopvariation is not sent
            if ($sTextId !== null && $sTextId !== '') {
                $this->aLoadedTextIds[$sIdFieldName] = $sTextId;
            }

            if ($sTextId !== null && $sTextId !== '') {
                // CRITICAL FIX: Skip loading longtext if we're in save() process
                // This prevents parent::save() → load() from overwriting NULL shopvariation
                // during batch form submit (where only first product gets form data)
                if ($this->skipLoadingLongText) {
                    continue; // Don't fetch from longtext table, preserve current value
                }

                try {
                    // Fetch from PrepareLongText table
                    $sJsonValue = MLDatabase::factory('amazon_preparelongtext', ML_Amazon_Model_Table_Amazon_PrepareLongText::class)->getValue($sTextId, $sNameLower);

                    if ($sJsonValue !== null && $sJsonValue !== '') {
                        // IMPORTANT: Store JSON string (NOT decoded array) in aData
                        // parent::get() will auto-decode it via MLHelper::getEncoderInstance()->decode()
                        $this->aData[$sFieldName] = $sJsonValue;
                    }
                } catch (Exception $oEx) {
                    MLMessage::gi()->addDebug('PrepareLongText load failed: ' . $oEx->getMessage());
                }
            }

            // LEGACY APPROACH: If no TextId, check legacy field
            if (!isset($this->aData[$sFieldName]) || $this->aData[$sFieldName] === null) {
                // Legacy field might already be auto-decoded by parent class
                // Just leave it as-is (parent::load() already handled it)
            }
        }

        return $return;
    }

    /**
     * Override save() to save long text data to PrepareLongText table
     *
     * New Transparent Approach:
     * 1. Before saving main record:
     *    - For each long text field in aData (e.g., ShopVariation)
     *    - Generate SHA256 hash from JSON
     *    - Check if TextId exists using model->load()
     *    - Only save if doesn't exist (no unnecessary INSERTs)
     *    - Update *Id field in aData (e.g., ShopVariationId)
     *    - Clear legacy field in aData (set to NULL)
     * 2. Save main record via parent::save()
     *
     * Benefits:
     * - Transparent: Works like normal field
     * - OOP Pattern: Uses Model instead of raw SQL
     * - Performance: Check first, insert only if needed
     * - No unnecessary queries: load() returns false if not exists
     * - Simple: No complex caching logic
     *
     * Deduplication:
     * - Multiple products can share same TextId (same hash)
     * - PrepareLongText acts as content-addressable storage
     * - load() checks existence, save() only if not found
     *
     * Cleanup:
     * - Old TextId cleanup NOT done here (performance)
     * - Batch cleanup should be done separately
     * - See reactActionSaveAttributeMatching() for bulk cleanup
     *
     * @return $this
     */
    public function save() {
        // CRITICAL FIX: If cache is empty and primary keys are set,
        // fetch existing TextId values from database BEFORE processing longtext fields
        // This handles batch form submit where load() is never called before save()
        //
        // Flow during form submit:
        // 1. model->set('mpid', 25)->set('productsid', 123)->set(fields...)->save()
        // 2. load() was NEVER called → aLoadedTextIds is EMPTY
        // 3. Form doesn't send shopvariation for products 2-4 → $mValue is NULL
        // 4. Without this fix: cache empty + $mValue null → TextId cleared to NULL
        // 5. With this fix: Fetch existing TextId from DB → preserve it
        if (empty($this->aLoadedTextIds)) {
            // Check if primary keys are set (indicates UPDATE, not INSERT)
            $bHasPrimaryKeys = true;
            foreach ($this->aKeys as $sKey) {
                if (!isset($this->aData[$sKey]) || $this->aData[$sKey] === null) {
                    $bHasPrimaryKeys = false;
                    break;
                }
            }

            // If primary keys exist, fetch existing TextId values from database
            if ($bHasPrimaryKeys) {
                foreach ($this->aLongTextFields as $sFieldName => $sIdFieldName) {
                    // Build WHERE clause from primary keys
                    $aWhere = array();
                    foreach ($this->aKeys as $sKey) {
                        $aWhere[] = $sKey . " = '" . MLDatabase::getDbInstance()->escape($this->aData[$sKey]) . "'";
                    }
                    $sWhere = implode(' AND ', $aWhere);

                    // Fetch existing *Id value from database
                    $sSql = "SELECT " . $sIdFieldName . " FROM " . $this->sTableName . " WHERE " . $sWhere . " LIMIT 1";

                    try {
                        $sExistingTextId = MLDatabase::getDbInstance()->fetchOne($sSql);
                        if ($sExistingTextId !== null && $sExistingTextId !== '') {
                            // Cache it so it can be preserved if form doesn't send new value
                            $this->aLoadedTextIds[$sIdFieldName] = $sExistingTextId;
                        }
                    } catch (Exception $oEx) {
                        MLMessage::gi()->addDebug('Failed to fetch existing TextId: ' . $oEx->getMessage());
                    }
                }
            }
        }

        // BEFORE saving main record, handle long text fields
        foreach ($this->aLongTextFields as $sFieldName => $sIdFieldName) {
            $sNameLower = strtolower($sFieldName);

            // Check if this field has data in aData
            $mValue = isset($this->aData[$sFieldName]) ? $this->aData[$sFieldName] : null;

            // Handle null or empty values
            if ($mValue === null || $mValue === '' || (is_array($mValue) && empty($mValue))) {
                // IMPORTANT: Check if TextId was loaded from database (cached in load())
                // During batch submit, if form doesn't send shopvariation for products 2,3,4...,
                // we must preserve the existing TextId (don't clear it)
                $sExistingTextId = isset($this->aLoadedTextIds[$sIdFieldName]) ? $this->aLoadedTextIds[$sIdFieldName] : null;

                if ($sExistingTextId !== null && $sExistingTextId !== '') {
                    // Preserve existing TextId (don't clear it when form data is missing)
                    // This handles batch submit where only first product gets form data
                    $this->aData[$sIdFieldName] = $sExistingTextId;
                    $this->aData[$sFieldName] = null; // Clear legacy field (use TextId approach)
                } else {
                    // No existing TextId, and no new data → Clear both fields
                    $this->aData[$sIdFieldName] = null;
                    $this->aData[$sFieldName] = null;
                }
                continue;
            }

            // Generate TextId (SHA256 hash)
            $sJsonValue = is_array($mValue) ? json_encode($mValue) : $mValue;
            $sTextId = hash('sha256', $sJsonValue);

            try {
                // Save to PrepareLongText table using model (better OOP approach)
                // Check if this TextId + ReferenceFieldName combination exists
                $oLongText = MLDatabase::factory('amazon_preparelongtext');
                $oLongText->set('textid', $sTextId)->set('referencefieldname', $sNameLower);

                // Only save if record doesn't exist (avoid unnecessary INSERT attempts)
                if (!$oLongText->exists()) {
                    $oLongText->set('value', $sJsonValue)->save();
                }
                // If exists, do nothing (deduplication - same content already stored)
            } catch (Exception $oEx) {
                MLMessage::gi()->addDebug('PrepareLongText save failed: ' . $oEx->getMessage());
            }

            // Update *Id field in aData
            $this->aData[$sIdFieldName] = $sTextId;

            // Clear legacy field in aData (use new approach)
            $this->aData[$sFieldName] = null;
        }

        // CRITICAL FIX: Set flag to prevent parent::save() → load() from reloading longtext
        // This prevents overwriting NULL shopvariation during batch form submit
        // Flow without flag:
        //   1. Override save() sets shopvariation = NULL (line 442)
        //   2. parent::save() calls load() at Abstract.php:326
        //   3. load() fetches shopvariation from longtext table (overwrites NULL)
        //   4. update() saves populated shopvariation (WRONG)
        // Flow with flag:
        //   1. Override save() sets shopvariation = NULL + flag = true
        //   2. parent::save() calls load()
        //   3. load() sees flag, skips longtext fetching (preserves NULL)
        //   4. update() saves NULL shopvariation (CORRECT)
        $this->skipLoadingLongText = true;

        // Now save main record (with updated *Id fields)
        $result = parent::save();

        // Reset flag for next operation
        $this->skipLoadingLongText = false;

        return $result;
    }
}
