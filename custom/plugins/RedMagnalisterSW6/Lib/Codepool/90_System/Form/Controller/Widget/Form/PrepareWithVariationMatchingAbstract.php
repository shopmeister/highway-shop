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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_PrepareAbstract');

abstract class ML_Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract extends ML_Form_Controller_Widget_Form_PrepareAbstract
{
    const UNLIMITED_ADDITIONAL_ATTRIBUTES = PHP_INT_MAX;

    protected $aParameters = array('controller');
    protected $shopAttributes;
    protected $numberOfMaxAdditionalAttributes = 0;
    protected $aMPAttributes;

    protected $variationCache = array();

    public function construct()
    {
        MLSettingRegistry::gi()->addJs('select2/select2.min.js');
        MLSettingRegistry::gi()->addJs('select2/i18n/'.strtolower(MLLanguage::gi()->getCurrentIsoCode().'.js'));
        MLSetting::gi()->add('aCss', array('select2/select2.min.css'), true);
        parent::construct();
        $this->oPrepareHelper->bIsSinglePrepare = $this->oSelectList->getCountTotal() === '1';
    }

    public function render()
    {
        $this->getFormWidget();
        return $this;
    }

    public function renderAjax() {
        $action = MLRequest::gi()->data('action') ? ucfirst(MLRequest::gi()->data('action')) : null;
        if ($action !== null && method_exists($this, "reactAction".ucfirst($action))) {
            try {
                header('Access-Control-Allow-Origin: *');
                header('Content-type: application/json');
                header('Cache-Control: no-cache, must-revalidate');

                $response = $this->{"reactAction".ucfirst($action)}();
            } catch (\Exception $ex) {
                $response = array('success' => false, 'error' => array($ex->getMessage(), $ex->getFile().':'.$ex->getLine(),$ex->getTraceAsString()));
            }
            $response['debug'] = array('request' => MLRequest::gi()->data(), 'sql' => MLDatabase::getDbInstance()->getTimePerQuery());
            echo json_encode($response);
            MagnalisterFunctions::stop();
        } else {
            parent::renderAjax();
        }
    }

    private function reactActionShopAttributes() {
        return $this->getShopAttributes();
    }

    private function reactActionGetShopAttributeValues() {
        $sAttributeCode = MLRequest::gi()->data('attributeCode');
        if (!empty($sAttributeCode)) {
            $values = MLFormHelper::getPrepareAMCommonInstance()->getShopAttributeValues($sAttributeCode);
            return array('success' => true, 'values' => $values);
        }
        throw new \Exception('Invalid attribute code');
    }

    /**
     * Save attribute matching from React component
     *
     * This method receives a single attribute's data from React and saves it to the database.
     * It handles:
     * - Adding/Updating attributes (action=save or action not specified)
     * - Deleting attributes (action=delete)
     *
     * IMPORTANT: We load the specific prepare record by mpID and ProductsID, then only set
     * ShopVariation field. This prevents other fields from being overwritten with config values.
     *
     * Each attribute is saved independently, so multiple attributes can coexist:
     * {"attr1": {...}, "attr2": {...}, "attr3": {...}}
     *
     * @return array Success response with message
     * @throws \Exception If parameters are invalid or save fails
     */
    private function reactActionSaveAttributeMatching() {
        $error = array();
        $selectedProducts = $this->oSelectList->getQueryObject()->getAll();

        $sAttributeKey = MLRequest::gi()->data('attributeKey');
        $sMainCategory = MLRequest::gi()->data('variationGroup'); // This is the main category (PrimaryCategory)
        $sActionType = MLRequest::gi()->data('actionType'); // 'save' or 'delete'
        $aAttributeDataRaw = MLRequest::gi()->data('attributeData');
        $sVariationThemeRaw = MLRequest::gi()->data('variationTheme'); // Optional variation theme

        // Decode attributeData if provided
        $aAttributeData = null;
        if ($aAttributeDataRaw !== null && $aAttributeDataRaw !== '') {
            $aAttributeData = json_decode($aAttributeDataRaw, true);
        }

        // Decode variationTheme if provided (e.g., "SIZE/COLOR")
        $sVariationTheme = null;
        if ($sVariationThemeRaw !== null && $sVariationThemeRaw !== '') {
            $sVariationTheme = $sVariationThemeRaw;
        }

        if (empty($sAttributeKey) || empty($sMainCategory)) {
            throw new \Exception('Invalid parameters: attributeKey and variationGroup are required');
        }

        $iMpId = MLModule::gi()->getMarketPlaceId();

        // Process single attribute save
        $result = $this->processSingleAttributeSave(
            $selectedProducts,
            $iMpId,
            $sAttributeKey,
            $sActionType,
            $aAttributeData,
            $sMainCategory,
            $sVariationTheme,
            $error
        );

        return array(
            'success' => empty($error),
            'message' => 'Attribute matching ' . ($sActionType === 'delete' ? 'deleted' : 'saved') . ' successfully',
            'action'  => $result['resultAction'],
            'error' => $error,
            'newTextId' => $result['newTextId'],
            'selectedProducts' => $selectedProducts
        );
    }

    /**
     * Save multiple attributes at once (batch save)
     *
     * All attributes are saved together in one ShopVariation JSON with single TextId
     * Request format: {attributesData: {"attr1": {...}, "attr2": {...}}}
     *
     * @return array Success response
     * @throws \Exception If parameters are invalid
     */
    private function reactActionSaveAttributeMatchingBatch() {
        $error = array();
        $selectedProducts = $this->oSelectList->getQueryObject()->getResult();

        $sMainCategory = MLRequest::gi()->data('variationGroup');
        $sVariationThemeRaw = MLRequest::gi()->data('variationTheme');
        $sActionType = MLRequest::gi()->data('actionType'); // 'save' or 'delete'
        $aAttributesDataRaw = MLRequest::gi()->data('attributesData');

        // Decode variationTheme if provided
        $sVariationTheme = null;
        if ($sVariationThemeRaw !== null && $sVariationThemeRaw !== '') {
            $sVariationTheme = $sVariationThemeRaw;
        }

        // Decode attributesData - this is an OBJECT {attr1: {...}, attr2: {...}}
        $aAttributesData = null;
        if ($aAttributesDataRaw !== null && $aAttributesDataRaw !== '') {
            $aAttributesData = json_decode($aAttributesDataRaw, true);
        }

        if (empty($sMainCategory) || empty($aAttributesData) || !is_array($aAttributesData)) {
            throw new \Exception('Invalid parameters: variationGroup and attributesData are required');
        }

        $iMpId = MLModule::gi()->getMarketPlaceId();

        // Use ALL attributes directly as ShopVariation
        // attributesData is already in correct format: {"attr1": {...}, "attr2": {...}}
        $aShopVariation = $aAttributesData;

        // Step 1: Collect old TextIds
        $aOldTextIds = $this->collectOldTextIds($selectedProducts, $iMpId, $error);

        // Step 2: Process first product with ALL attributes at once
        $sFirstProductId = reset($selectedProducts)['pID'];
        $sNewTextId = null;
        $aPrepareData = null;
        $resultAction = array();

        try {
            $oPrepare = MLDatabase::factory('amazon_prepare');
            $oPrepare->set('mpid', $iMpId);
            $oPrepare->set('productsid', $sFirstProductId);
            $oPrepare->set('preparetype', $this->getSelectionNameValue());

            // Set ShopVariation with ALL attributes
            $oPrepare->set('ShopVariation', $aShopVariation);

            // Set MainCategory
            $oPrepare->set($oPrepare->getPrimaryCategoryFieldName(), $sMainCategory);

            // Set variation_theme if provided
            if ($sVariationTheme !== null && $sVariationTheme !== '') {
                $aVariationTheme = array($sVariationTheme => array());
                $oPrepare->set('variation_theme', json_encode($aVariationTheme));
            }

            // Mark for re-verification
            $oPrepare->set('Verified', 'ERROR');

            $oPrepare->save();

            // Get generated TextId
            $sNewTextId = $oPrepare->get('ShopVariationId');

            // Get prepare data for batch update
            if ($oPrepare->exists()) {
                $aPrepareData = $oPrepare->data(true, false);
            }

            $resultAction[$sFirstProductId] = 'saved';

        } catch (Exception $e) {
            $error[] = array(
                'product' => $sFirstProductId,
                'error' => $e->getMessage()
            );
        }

        // Step 3: Batch update remaining products
        if ($sNewTextId && count($selectedProducts) > 1) {
            $aBatchResult = $this->batchUpdateRemainingProducts(
                $selectedProducts,
                $iMpId,
                $sNewTextId,
                $aPrepareData,
                $sActionType,
                $sMainCategory,
                $sVariationTheme,
                $error
            );
            $resultAction = array_merge($resultAction, $aBatchResult);
        }

        // Step 4: Cleanup unused TextIds
        $this->cleanupUnusedTextIds($aOldTextIds, $error);

        return array(
            'success' => empty($error),
            'message' => 'Batch attribute matching saved successfully',
            'action' => $resultAction,
            'error' => $error,
            'newTextId' => $sNewTextId,
            'selectedProducts' => $selectedProducts
        );
    }

    /**
     * Process single attribute save - common logic extracted from reactActionSaveAttributeMatching
     *
     * @param array $selectedProducts Selected products
     * @param int $iMpId Marketplace ID
     * @param string $sAttributeKey Attribute key
     * @param string $sActionType Action type ('save' or 'delete')
     * @param array|null $aAttributeData Attribute data
     * @param string $sMainCategory Main category
     * @param string|null $sVariationTheme Variation theme
     * @param array &$error Error array reference
     * @return array Result with newTextId, prepareData, resultAction
     */
    private function processSingleAttributeSave($selectedProducts, $iMpId, $sAttributeKey, $sActionType, $aAttributeData, $sMainCategory, $sVariationTheme, &$error) {
        // Step 1: Collect old TextIds
        $aOldTextIds = $this->collectOldTextIds($selectedProducts, $iMpId, $error);

        // Step 2: Process the first product
        $aFirstProductResult = $this->processFirstProduct(
            reset($selectedProducts),
            $iMpId,
            $sAttributeKey,
            $sActionType,
            $aAttributeData,
            $sMainCategory,
            $sVariationTheme,
            $error
        );

        $sNewTextId = $aFirstProductResult['newTextId'];
        $aPrepareData = $aFirstProductResult['prepareData'];
        $resultAction = $aFirstProductResult['resultAction'];

        // Step 3: Batch update remaining products
        if ($sNewTextId && count($selectedProducts) > 1) {
            $aBatchResult = $this->batchUpdateRemainingProducts(
                $selectedProducts,
                $iMpId,
                $sNewTextId,
                $aPrepareData,
                $sActionType,
                $sMainCategory,
                $sVariationTheme,
                $error
            );
            $resultAction = array_merge($resultAction, $aBatchResult);
        }

        // Step 4: Cleanup unused TextIds
        $this->cleanupUnusedTextIds($aOldTextIds, $error);

        return array(
            'newTextId' => $sNewTextId,
            'prepareData' => $aPrepareData,
            'resultAction' => $resultAction
        );
    }

    /**
     * Step 1: Collect old TextIds from selected products using single query
     *
     * @param array $selectedProducts List of selected products
     * @param int $iMpId Marketplace ID
     * @param array &$error Error array reference
     * @return array Old TextIds (TextId => true)
     */
    private function collectOldTextIds($selectedProducts, $iMpId, &$error) {
        $aOldTextIds = array();

        try {
            $aProductIds = array();
            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['pID'])) {
                    $aProductIds[] = $selectedProduct['pID'];
                }
            }

            if (!empty($aProductIds)) {
                // Single query to get all old TextIds for selected products
                $sProductIdsPlaceholders = "'" . implode("','", array_map(array(MLDatabase::getDbInstance(), 'escape'), $aProductIds)) . "'";
                $sSql = "SELECT DISTINCT ShopVariationId
                         FROM magnalister_amazon_prepare
                         WHERE mpID = '" . MLDatabase::getDbInstance()->escape($iMpId) . "'
                         AND ProductsID IN ({$sProductIdsPlaceholders})
                         AND ShopVariationId IS NOT NULL
                         AND ShopVariationId != ''";

                $aOldTextIdRows = MLDatabase::getDbInstance()->fetchArray($sSql);
                foreach ($aOldTextIdRows as $aRow) {
                    $aOldTextIds[$aRow['ShopVariationId']] = true;
                }
            }
        } catch (Exception $e) {
            $error[] = array(
                'step' => 'collect_old_textids',
                'error' => $e->getMessage()
            );
        }

        return $aOldTextIds;
    }

    /**
     * Step 2: Process first product to generate new TextId
     *
     * @param array $firstProduct First product data
     * @param int $iMpId Marketplace ID
     * @param string $sAttributeKey Attribute key to save/delete
     * @param string $sActionType 'save' or 'delete'
     * @param array|null $aAttributeData Attribute data to save
     * @param string $sMainCategory Primary category (main category)
     * @param string|null $sVariationTheme Variation theme (e.g., "SIZE/COLOR")
     * @param array &$error Error array reference
     * @return array Array with 'newTextId' and 'resultAction'
     */
    private function processFirstProduct($firstProduct, $iMpId, $sAttributeKey, $sActionType, $aAttributeData, $sMainCategory, $sVariationTheme, &$error) {
        $sNewTextId = null;
        $resultAction = array();

        if (!$firstProduct) {
            return array('newTextId' => $sNewTextId, 'resultAction' => $resultAction);
        }

        try {
            $sProductId = $firstProduct['pID'];

            /** @var $oPrepare ML_Amazon_Model_Table_Amazon_Prepare */
            $oPrepare = MLDatabase::factory('amazon_prepare');

            /**
             * IMPORTANT: Load existing ShopVariation data directly from database
             *
             * WHY THIS APPROACH IS NEEDED:
             * The Prepare model has a critical bug when loading existing records:
             * - When you call set('mpid', ...) and set('productsid', ...) BEFORE calling get()
             * - The model's internal load() mechanism gets confused
             * - Even though exists() returns true, get('ShopVariation') returns NULL
             * - The aData array gets populated with keys but NULL values
             *
             * SYMPTOMS OF THIS BUG:
             * - Database shows ShopVariationId = "abc123..." (has value)
             * - $oPrepare->get('ShopVariationId') returns NULL
             * - $oPrepare->get('ShopVariation') returns NULL
             * - Raw SQL query shows data exists
             * - Model's aData array has the field name but value is NULL
             *
             * ROOT CAUSE:
             * The parent model class (ML_Database_Model_Table_Abstract) has a complex
             * load() mechanism that gets disrupted when key fields are set() before load().
             * When set() is called on key fields:
             * 1. The model marks those fields as "modified" in memory
             * 2. When get() is called, it triggers load()
             * 3. But load() sees some fields are already "set", so it doesn't fully populate aData
             * 4. Result: Fields exist in aData but with NULL values
             *
             * SOLUTION:
             * Bypass the model entirely for loading:
             * 1. Query database directly to get ShopVariationId
             * 2. Load from PrepareLongText directly using that TextId
             * 3. Then use the model ONLY for saving (not loading)
             *
             * HOW TO IDENTIFY SIMILAR ISSUES:
             * - Symptoms: get() returns NULL but database has data
             * - Debug: Check if set() was called on key fields before get()
             * - Solution: Load data directly via SQL before using model
             *
             * LESSON LEARNED:
             * Never call set() on key fields before calling get() on other fields
             * in the same model instance. Either:
             * A) Load first, then set() and save()
             * B) Bypass model's get() and query database directly (this approach)
             */
            $sSql = "SELECT ShopVariationId FROM magnalister_amazon_prepare
                     WHERE mpID = '" . MLDatabase::getDbInstance()->escape($iMpId) . "'
                     AND ProductsID = '" . MLDatabase::getDbInstance()->escape($sProductId) . "'";
            $aExistingRow = MLDatabase::getDbInstance()->fetchRow($sSql);

            $aShopVariation = array();
            if ($aExistingRow && !empty($aExistingRow['ShopVariationId'])) {
                // Load existing data from PrepareLongText table
                $sExistingTextId = $aExistingRow['ShopVariationId'];

                try {
                    $oLongText = MLDatabase::factory('amazon_preparelongtext');
                    $oLongText->set('TextId', $sExistingTextId);
                    $oLongText->set('ReferenceFieldName', 'shopvariation');
                    if ($oLongText->exists()) {
                        $aShopVariation = $oLongText->get('Value');
                    }
                } catch (Exception $oEx) {
                    // If loading fails, start with empty array
                    $aShopVariation = array();
                }
            }

            /**
             * IMPORTANT: If ShopVariation is empty in amazon_prepare, load from amazon_variationmatching
             *
             * WHY THIS IS NEEDED:
             * When user first opens the page, attribute matching data might only exist in
             * amazon_variationmatching table (not yet in amazon_prepare). If user then changes
             * just one attribute, we need to preserve all other attributes from variationmatching.
             *
             * SCENARIO:
             * 1. User opens page - attributes loaded from amazon_variationmatching
             * 2. User changes attribute "color" only
             * 3. Without this check: Only "color" would be saved, other attributes lost
             * 4. With this check: Load all attributes from variationmatching, then merge "color" change
             *
             * SOLUTION:
             * If amazon_prepare has no ShopVariation data, check amazon_variationmatching table
             * and load existing attribute matching from there as base data.
             */
            if (empty($aShopVariation)) {
                $sCustomIdentifier = ''; // Can be extended to support custom identifiers if needed
                $aShopVariation = $this->getAttributesFromDB($sMainCategory, $sCustomIdentifier);
                if (!is_array($aShopVariation)) {
                    $aShopVariation = array();
                }
            }

            // Now set up the model for saving (not loading!)
            $oPrepare->set('mpid', $iMpId);
            $oPrepare->set('productsid', $sProductId);
            $oPrepare->set('preparetype', $this->getSelectionNameValue());

            // Apply action (delete or save)
            if ($sActionType === 'delete') {
                if (isset($aShopVariation[$sAttributeKey])) {
                    unset($aShopVariation[$sAttributeKey]);
                    $resultAction[$sProductId] = 'deleted';
                }
            } else {
                $aShopVariation[$sAttributeKey] = $aAttributeData;
                $resultAction[$sProductId] = 'updated';
            }

            // Now set preparetype AFTER loading existing data
            $oPrepare->set('preparetype', $this->getSelectionNameValue());
            // Save ShopVariation
            $oPrepare->set('ShopVariation', $aShopVariation);

            // Save MainCategory (MainCategory)
            $oPrepare->set($oPrepare->getPrimaryCategoryFieldName(), $sMainCategory);

            // Save variation_theme if provided (as JSON: {"SIZE/COLOR":[]})
            if ($sVariationTheme !== null && $sVariationTheme !== '') {
                $aVariationTheme = array($sVariationTheme => array());
                $oPrepare->set('variation_theme', json_encode($aVariationTheme));
            }

            // Mark product for re-verification since attribute matching changed
            $oPrepare->set('Verified', 'ERROR');

            $oPrepare->save();

            // Get the new TextId that was generated
            $sNewTextId = $oPrepare->get('ShopVariationId');

            // Get all prepare data from first product to use as template for remaining products
            $aPrepareData = null;
            if ($oPrepare->exists()) {
                $aPrepareData = $oPrepare->data(true, false);
            }

        } catch (Exception $e) {
            $error[] = array(
                'product' => $sProductId,
                'error' => $e->getMessage(),
                'errorTrace' => $e->getFile().':'.$e->getLine()."\n".$e->getTraceAsString()
            );
        }

        return array(
            'newTextId'    => $sNewTextId,
            'prepareData'  => $aPrepareData,
            'resultAction' => $resultAction
        );
    }

    /**
     * Step 3: Batch update remaining products with same TextId
     *
     * Uses the complete data from the first product as a template, only changing ProductsID
     * for each remaining product. This ensures all fields from the first product (Description,
     * Bulletpoint, etc.) are copied to remaining products.
     *
     * @param array $selectedProducts All selected products
     * @param int $iMpId Marketplace ID
     * @param string $sNewTextId New TextId to assign
     * @param array|null $aPrepareData Complete prepare data from first product (from $oPrepare->data())
     * @param string $sActionType 'save' or 'delete'
     * @param string $sMainCategory Primary category (main category)
     * @param string|null $sVariationTheme Variation theme (e.g., "SIZE/COLOR")
     * @param array &$error Error array reference
     * @return array Result actions for updated products
     */
    private function batchUpdateRemainingProducts($selectedProducts, $iMpId, $sNewTextId, $aPrepareData, $sActionType, $sMainCategory, $sVariationTheme, &$error) {
        $resultAction = array();
        $batchUpdateData = array();

        // Prepare batch update data for remaining products
        $remainingProducts = array_slice($selectedProducts, 1);

        $oPrepare = MLDatabase::factory('amazon_prepare');
        foreach ($remainingProducts as $selectedProduct) {
            $sProductId = $selectedProduct['pID'];
            $aProductData = array(
                'mpID' => $iMpId,
                'ProductsID' => $sProductId,
                'ShopVariationId' => $sNewTextId,
                'ShopVariation' => null,
                $oPrepare->getPrimaryCategoryFieldName() => $sMainCategory,
                'Verified'      => 'ERROR',
                'PreparedTS'    => date('Y-m-d H:i:s'),
                'PrepareType'   => $this->getSelectionNameValue(),
            );

            // Add variation_theme if provided
            if ($sVariationTheme !== null && $sVariationTheme !== '') {
                $aVariationTheme = array($sVariationTheme => array());
                $aProductData['variation_theme'] = json_encode($aVariationTheme);
            }


            $batchUpdateData[] = $aProductData;
            $resultAction[$sProductId] = $sActionType === 'delete' ? 'deleted' : 'updated';
        }

        // Execute batch update using INSERT ... ON DUPLICATE KEY UPDATE
        if (!empty($batchUpdateData)) {
            try {
                // Get field names from actual batch data (not from $aPrepareData which may have extra fields)
                // Use the first record in batch as template
                $aFieldsToUpdate = !empty($batchUpdateData[0]) ? array_keys($batchUpdateData[0]) : array(
                    'ShopVariationId',
                    'ShopVariation',
                    $oPrepare->getPrimaryCategoryFieldName(),
                    'Verified',
                    'PreparedTS',
                    'variation_theme'
                );

                // Exclude primary keys from update list
                $aFieldsToUpdate = array_diff($aFieldsToUpdate, array('mpID', 'ProductsID'));

                MLDatabase::getDbInstance()->batchinsert(
                    'magnalister_amazon_prepare',
                    $batchUpdateData, false, // Don't use REPLACE
                    array_values($aFieldsToUpdate) // Use INSERT ... ON DUPLICATE KEY UPDATE
                );
            } catch (Exception $e) {
                $error[] = array(
                    'batch_update' => 'failed',
                    'error' => $e->getMessage()
                );
            }
        }

        return $resultAction;
    }

    /**
     * Step 4: Delete unused old TextIds from PrepareLongText
     *
     * @param array $aOldTextIds Old TextIds to check (TextId => true)
     * @param array &$error Error array reference
     */
    private function cleanupUnusedTextIds($aOldTextIds, &$error) {
        if (empty($aOldTextIds)) {
            return;
        }

        try {
            $aOldTextIdsList = array_keys($aOldTextIds);

            // Check which old TextIds are still being used by ANY product
            $sTextIdsPlaceholders = "'" . implode("','", array_map(array(MLDatabase::getDbInstance(), 'escape'), $aOldTextIdsList)) . "'";
            $sSql = "SELECT ShopVariationId, COUNT(*) as cnt
                     FROM magnalister_amazon_prepare
                     WHERE ShopVariationId IN ({$sTextIdsPlaceholders})
                     GROUP BY ShopVariationId";

            $aStillUsed = MLDatabase::getDbInstance()->fetchArray($sSql);
            $aStillUsedSet = array();
            foreach ($aStillUsed as $aRow) {
                $aStillUsedSet[$aRow['ShopVariationId']] = true;
            }

            // Delete TextIds that are no longer used by any product
            foreach ($aOldTextIdsList as $sOldTextId) {
                if (!isset($aStillUsedSet[$sOldTextId])) {
                    $oLongText = MLDatabase::factory('amazon_preparelongtext');
                    $oLongText->set('TextId', $sOldTextId);
                    $oLongText->set('ReferenceFieldName', 'shopvariation');
                    if ($oLongText->exists()) {
                        $oLongText->delete();
                    }
                }
            }
        } catch (Exception $oEx) {
            $error[] = array(
                'cleanup' => 'failed',
                'error' => $oEx->getMessage()
            );
        }
    }


    public function getRequestField($sName = null, $blOptional = false)
    {
        if (count($this->aRequestFields) == 0) {
            $this->aRequestFields = $this->getRequest($this->sFieldPrefix);
            $this->aRequestFields = is_array($this->aRequestFields) ? $this->aRequestFields : array();
        }

        return parent::getRequestField($sName, $blOptional);
    }

    /**
     * @return int
     */
    public function getNumberOfMaxAdditionalAttributes()
    {
        return $this->numberOfMaxAdditionalAttributes;
    }

    protected function getSelectionNameValue()
    {
        return 'apply';
    }

    public function getModificationDate()
    {
        $aRows = $this->oPrepareList->getList();
        return count($aRows) > 0 ? current($aRows)->get(MLDatabase::getPrepareTableInstance()->getPreparedTimestampFieldName()) : '';
    }

    protected function getCustomIdentifier()
    {
        $sCustomIdentifier = $this->getRequestField('customidentifier');
        return !empty($sCustomIdentifier) ? $sCustomIdentifier : '';
    }

    /**
     * Remove Hint like "Zusatzfelder:" or "Eigenschaften:"
     * @param $aValue
     */
    protected function removeCustomAttributeHint(&$aValue) {
        // if custom attribute and not empty
        if (isset($aValue['CustomAttributeNameCode']) && $aValue['Code'] != '') {
            $sDelimiter = ': ';
            $aExplode = explode($sDelimiter, $aValue['AttributeName']);
            if (!empty($aExplode)) {
                $aValue['AttributeName']= str_replace($aExplode[0].$sDelimiter, '', $aValue['AttributeName']);
            }
        }
    }

    protected function validateCustomAttributes(
        $key,
        &$value,
        &$previouslyMatchedAttributes,
        &$aErrors,
        &$emptyCustomName,
        $savePrepare,
        $isSelectedAttribute,
        &$numberOfMatchedAdditionalAttributes
    ) {
        if (!isset($value['CustomAttributeNameCode']) || $value['Code'] == '') {
            $previouslyMatchedAttributes[$key] = $value;
            return;
        }

        $invalidName = false;
        $numberOfMatchedAdditionalAttributes++;

        if (empty($value['AttributeName'])) {
            if ($this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)) {
                $value['Error'] = true;
            }

            if (!$emptyCustomName && $savePrepare) {
                $aErrors[] = self::getMessage('_prepare_variations_error_empty_custom_attribute_name');
            }
            $emptyCustomName = true;

        } else {
            foreach ($previouslyMatchedAttributes as $previouslyMatchedAttribute) {
                if ($previouslyMatchedAttribute['AttributeName'] === $value['AttributeName']) {
                    $invalidName = true;
                    break;
                }
            }

            if ($invalidName && $this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)) {
                $value['Error'] = true;
                if ($savePrepare) {
                    $aErrors[] = self::getMessage(
                        '_prepare_variations_error_duplicated_custom_attribute_name',
                        array(
                            'attributeName' => $value['AttributeName'],
                            'marketplace' => MLModule::gi()->getMarketPlaceName(false),
                        )
                    );
                }
            }
        }
        $previouslyMatchedAttributes[$key] = $value;
    }

    protected function getCategoryIdentifierValue()
    {
        $aMatching = $this->getRequestField();
        return isset($aMatching['variationgroups.value']) ? $aMatching['variationgroups.value'] : '';
    }

    /**
     * Checks if prepare has any errors which should be considered only if $savePrepare === true (button for saving all
     * data from form is pressed).
     *
     * @param $savePrepare Bool False or code of submitted attribute
     * @return bool
     */
    protected function prepareHasErrors($savePrepare)
    {
        return $savePrepare && !empty($this->oPrepareHelper->aErrors);
    }

    /**
     * Sets prepared status to error if there are any errors
     */
    protected function setPreparedStatusToError()
    {
        $productIDs = array();
        foreach ($this->oPrepareHelper->aErrors as $error) {
            if (is_array($error)) {
                $productIDs[] = $error['product_id'];
                $error = $error['message'];
            }
            MLMessage::gi()->addError(MLI18n::gi()->get($error), null, !MLHttp::gi()->isAjax());
            MLMessage::gi()->addDebug(MLI18n::gi()->get($error));

        }

        $this->setPreparedStatus(false, $productIDs);
    }

    /**
     * Gets the data that is needed for proper validation of attributes when there is some variation theme chosen. All
     * attributes that make variation theme and variation theme code are needed for validation. Code is needed because
     * if code for splitting all variations, or 'null' code is submitted, validation should not be done.
     *
     * @param array $aMatching Used for transmitting variation theme data
     * @return array Data that is needed for variation theme validation
     */
    protected function getVariationThemeValidationData($aMatching)
    {
        $variationThemeAttributes = null;
        $submittedVariationThemeCode = '';

        if (isset($aMatching['variationthemealldata'])) {
            $variationThemes = json_decode(htmlspecialchars_decode($aMatching['variationthemealldata']), true);

            $submittedVariationTheme = array();
            if (isset($aMatching['variationthemecode']) && is_array($aMatching['variationthemecode'])) {
                // When submitting ajax field in V3 submitted value is an array. That array has format :
                // variationthemecode => array($codeOfDependingField => $variationThemeCode);
                $submittedVariationTheme = array_values($aMatching['variationthemecode']);
            }

            $submittedVariationThemeCode = reset($submittedVariationTheme);
            $variationThemeAttributes = array();
            if (isset($variationThemes[$submittedVariationThemeCode]['attributes'])) {
                $variationThemeAttributes = $variationThemes[$submittedVariationThemeCode]['attributes'];

            }
        }

        return array(
            'variationThemeAttributes' => $variationThemeAttributes,
            'submittedVariationThemeCode' => $submittedVariationThemeCode,
        );
    }

    /**
     * Saves variation theme black list. In some marketplaces there is list of attributes that can not be used as
     * variation ones. They are saved in prepare table because later will be used for making the addItems request(split
     * and skip)
     *
     * @param array $aMatching Used for transmitting variation theme blacklist data
     */
    protected function saveVariationThemeBlacklist(&$aMatching)
    {
        if (isset($aMatching['variationthemeblacklist'])) {
            $variationThemeBlacklistHTMLDecoded = htmlspecialchars_decode($aMatching['variationthemeblacklist']);
            $variationThemeBlacklist = json_decode($variationThemeBlacklistHTMLDecoded, true);

            $this->oPrepareList->set('VariationThemeBlacklist', $variationThemeBlacklist);
            unset($aMatching['variationthemeblacklist']);
        }
    }

    /**
     * @param array $aMatching
     * @param string $identifier
     * @return string
     */
    protected function getIdentifier($aMatching)
    {
        $identifier = $this->getCategoryIdentifierValue();
        if (empty($identifier) && !empty($aMatching['variationgroups'])) {
            $variationGroupKeys = array_keys($aMatching['variationgroups']);
            $identifier = array_shift($variationGroupKeys);
        }

        if ($identifier === 'new') {
            $identifier = $aMatching['variationgroups.code'];
        }

        return $identifier;
    }

    protected function attributeIsMatched($value) {
        return  $value['Code'] !== '' && (!empty($value['Values']) || (isset($value['Values']) && $value['Values'] === '0'));
    }

    /**
     * Sets validated data for mandatory or variation theme attributes.
     *
     * @param array $attributeProperties Properties of an attribute that is being validated
     * @param bool $isVariationThemeAttribute Flag for checking whether it is an attribute from variation theme
     * @param mixed $savePrepare Bool False or code of submitted attribute
     * @param bool $isSelectedAttribute Flag representing whether it is an attribute on which save or delete is invoked
     * @param string $attributeName Name of an attribute needed for error log
     * @param array $aMatching Whole matching
     * @param array $aErrors Whole errors array that will be returned to client
     * @param string $key Code for unset from matching
     */
    protected function setValidatedDataForRequiredOrVariationThemeAttribute(
        &$attributeProperties,
        $isVariationThemeAttribute,
        $savePrepare,
        $isSelectedAttribute,
        $attributeName,
        &$aMatching,
        &$aErrors,
        $key
    ) {
        if ($this->attributeIsMatched($attributeProperties)) {
            return;
        }

        if ($this->isRequiredAttribute($attributeProperties, $isVariationThemeAttribute)) {
            $this->setPreparedStatus(false);

            if ($this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)) {
                $attributeProperties['Error'] = true;

                if ($savePrepare) {
                    $aErrors = $this->setMissingRequiredAttrbiteError($attributeName, $aErrors);
                }
            }
        }

        // $key should be unset whenever item does not have any errors and condition
        // (isset($value['Required']) && $value['Required'] && $savePrepare) is not true. That way only required data
        // or data with errors will be saved to DB.
        if ((!$this->isRequiredAttribute($attributeProperties, $isVariationThemeAttribute) || !$savePrepare) &&
            empty($attributeProperties['Error'])
        ) {
            unset($aMatching[$key]);
        }

        // Unset previous values if code is empty (can happen when user click on "-" attribute button
        $attributeProperties['Values'] = array();
    }

    /**
     * Sets attribute properties when no selection code is submitted.
     *
     * @param array $attributeProperties Properties of an attribute that is being validated
     * @param bool $isVariationThemeAttribute Flag for checking whether it is an attribute from variation theme
     * @param mixed $savePrepare Bool False or code of submitted attribute
     * @param bool $isSelectedAttribute Flag representing whether it is an attribute on which save or delete is invoked
     * @param string $attributeName Name of an attribute needed for error log
     * @param array $aErrors Whole errors array that will be returned to client
     */
    protected function setValidatedNoSelectionAttribute(
        &$attributeProperties,
        $isVariationThemeAttribute,
        $savePrepare,
        $isSelectedAttribute,
        $attributeName,
        &$aErrors
    ) {
        if ($attributeProperties['Values']['0']['Shop']['Key'] !== 'noselection' &&
            $attributeProperties['Values']['0']['Marketplace']['Key'] !== 'noselection'
        ) {
            return;
        }

        unset($attributeProperties['Values']['0']);

        if (empty($attributeProperties['Values']) &&
            $this->isRequiredAttribute($attributeProperties, $isVariationThemeAttribute) &&
            $this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)
        ) {
            if ($savePrepare) {
                $aErrors[] = self::getMessage('_prepare_variations_error_text',
                    array('attribute_name' => $attributeName));
            }
            $attributeProperties['Error'] = true;
        }

        foreach ($attributeProperties['Values'] as $k => &$v) {
            if (empty($v['Marketplace']['Info']) || $v['Marketplace']['Key'] === 'manual') {
                $v['Marketplace']['Info'] = $v['Marketplace']['Value'] .
                    self::getMessage('_prepare_variations_free_text_add');
            }
        }
    }

    protected function isRequiredAttribute($attributeProperties, $isVariationThemeAttribute)
    {
        return (isset($attributeProperties['Required']) && $attributeProperties['Required']) || $isVariationThemeAttribute;
    }

    protected function shouldValidateAttribute($savePrepare, $isSelectedAttribute)
    {
        return $savePrepare || $isSelectedAttribute;
    }

    /**
     * Sets attribute values depending on shop key.
     *
     * @param array $attributeProperties
     * @param string $info
     */
    protected function setAttributeValues(&$attributeProperties, $info)
    {
        if ($attributeProperties['Values']['0']['Shop']['Key'] === 'all') {
            $newValue = array();
            $i = 0;
            $matchedMpValue = $attributeProperties['Values']['0']['Marketplace']['Value'];

            foreach ($this->getShopAttributeValues($attributeProperties['Code']) as $keyAttribute => $valueAttribute) {
                $newValue[$i]['Shop']['Key'] = $keyAttribute;
                $newValue[$i]['Shop']['Value'] = $valueAttribute;
                $newValue[$i]['Marketplace']['Key'] = $attributeProperties['Values']['0']['Marketplace']['Key'];
                $newValue[$i]['Marketplace']['Value'] = $attributeProperties['Values']['0']['Marketplace']['Key'];
                // $matchedMpValue can be array if it is multi value, so that`s why this is checked and converted to
                // string if it is. That is done because this information will be displayed in matched table.
                $newValue[$i]['Marketplace']['Info'] = (is_array($matchedMpValue) ? implode(', ', $matchedMpValue)
                        : $matchedMpValue) . $info;
                $i++;
            }

            $attributeProperties['Values'] = $newValue;
        } else {
            foreach ($attributeProperties['Values'] as $k => &$v) {
                if (empty($v['Marketplace']['Info'])) {
                    // $v['Marketplace']['Value'] can be array if it is multi value, so that`s why this is checked
                    // and converted to string if it is. That is done because this information will be displayed in
                    // matched table.
                    $v['Marketplace']['Info'] = (is_array($v['Marketplace']['Value']) ?
                            implode(', ', $v['Marketplace']['Value'])  : $v['Marketplace']['Value']) . $info;
                }

                if ($v['Marketplace']['Key'] === 'manual') {
                    $v['Marketplace']['Key'] = $v['Marketplace']['Value'];

                } else if ($v['Marketplace']['Key'] === 'notmatch') {//to keep not match in matching here we shouldn't do anything

                } else {
                    $v['Marketplace']['Value'] = $v['Marketplace']['Key'];
                }
            }
        }

        $attributeProperties['Values'] = $this->fixAttributeValues($attributeProperties['Values']);
    }

    /**
     * Saves shop variation and chosen category to DB.
     *
     * @param array $shopVariation
     * @param string $category
     */
    protected function saveShopVariationAndPrimaryCategory($shopVariation, $category)
    {
        $oPrepareTable = MLDatabase::getPrepareTableInstance();
        $this->oPrepareList->set('shopvariation', json_encode($shopVariation));
        // for first preparation we should add calculated shopvariaton to request field
        // otherwise it try to read it from prepare table, but prepare table is always empty in first preparation
        $this->aRequestFields['shopvariation'] = $shopVariation;
        $this->oPrepareHelper->setRequestFields($this->aRequestFields);
        $this->oPrepareList->set($oPrepareTable->getPrimaryCategoryFieldName(), $category);
    }

    /**
     * @param array $errors
     *
     */
    protected function setAllErrorsAndPreparedStatus($errors) {
        if (!empty($errors)) {
            foreach ($errors as $error) {
                MLMessage::gi()->addError($error, null, !MLHttp::gi()->isAjax());
            }
            $this->setPreparedStatus(false);
        }
    }

    /**
     * Check if prepare has errors and handle error status
     *
     * Extracted general logic from triggerBeforeFinalizePrepareAction
     * @param mixed $savePrepare Bool False or code of submitted attribute
     * @return bool True if has errors, false otherwise
     */
    protected function processPrepareErrorsCheck($savePrepare) {
        if ($this->prepareHasErrors($savePrepare)) {
            $this->setPreparedStatusToError();
            return true;
        }
        return false;
    }

    /**
     * Process and save variation theme data
     *
     * Extracted general logic from triggerBeforeFinalizePrepareAction
     * @param array $aMatching Request matching data (passed by reference, modified)
     * @return array Array with keys: 'variationThemeAttributes', 'submittedVariationThemeCode', 'variationThemeExists'
     */
    protected function processVariationThemeData(&$aMatching) {
        $variationThemeData = $this->getVariationThemeValidationData($aMatching);
        $variationThemeAttributes = $variationThemeData['variationThemeAttributes'];
        $submittedVariationThemeCode = $variationThemeData['submittedVariationThemeCode'];

        $variationThemeExists = isset($aMatching['variationthemealldata']);
        if ($variationThemeExists) {
            // Save variation theme to prepare table and it will be later used for making addItems request(split & skip)
            $this->oPrepareList->set('variation_theme', json_encode(array($submittedVariationThemeCode => $variationThemeAttributes), true));
            unset($aMatching['variationthemecode']);
            unset($aMatching['variationthemealldata']);
        }

        $this->saveVariationThemeBlacklist($aMatching);

        return array(
            'variationThemeAttributes'    => $variationThemeAttributes,
            'submittedVariationThemeCode' => $submittedVariationThemeCode,
            'variationThemeExists'        => $variationThemeExists
        );
    }

    /**
     * Validate and get category identifier
     *
     * Extracted general logic from triggerBeforeFinalizePrepareAction
     * @param array $aMatching Request matching data
     * @return string|false Category identifier or false if validation failed
     */
    protected function validateAndGetCategoryIdentifier($aMatching) {
        $sIdentifier = $this->getIdentifier($aMatching);

        if (empty($sIdentifier)) {
            MLMessage::gi()->addError(MLI18n::gi()->get(self::getMPName() . '_prepareform_category'), null, !MLHttp::gi()->isAjax());
            $this->setPreparedStatus(false);
            return false;
        }

        return $sIdentifier;
    }

    /**
     * Process general validations (max attributes, variation theme mandatory check)
     *
     * Extracted general logic from triggerBeforeFinalizePrepareAction
     * @param mixed $savePrepare Bool False or code of submitted attribute
     * @param int $numberOfMatchedAdditionalAttributes Current number of matched additional attributes
     * @param int $maxNumberOfAdditionalAttributes Maximum allowed additional attributes
     * @param string $submittedVariationThemeCode Submitted variation theme code
     * @param array $aErrors Errors array (passed by reference)
     */
    protected function processGeneralValidations($savePrepare, $numberOfMatchedAdditionalAttributes, $maxNumberOfAdditionalAttributes, $submittedVariationThemeCode, &$aErrors) {
        if ($savePrepare && $numberOfMatchedAdditionalAttributes > $maxNumberOfAdditionalAttributes) {
            // If there is a limit on number of custom attributes, validation message should be displayed.
            $aErrors[] = self::getMessage('_prepare_variations_error_maximal_number_custom_attributes_exceeded', array('numberOfAttributes' => $maxNumberOfAdditionalAttributes));
        }

        // If variation theme is defined for that category and mandatory but nothing is selected.
        if ($submittedVariationThemeCode === 'null') {
            $aErrors[] = self::getMessage('_prepare_variations_theme_mandatory_error');
        }
    }

    /**
     * Finalize preparation: save data and handle errors
     *
     * Extracted general logic from triggerBeforeFinalizePrepareAction
     * @param mixed $savePrepare Bool False or code of submitted attribute
     * @param array $aErrors Errors array
     * @param ML_Database_Model_Table_VariantMatching_Abstract|null $oVariantMatching Variant matching DB object
     * @param string $sIdentifier Category identifier
     * @param string $sCustomIdentifier Custom identifier
     * @param array $aMatching Request matching data
     * @return bool True if preparation completed successfully, false otherwise
     */
    protected function finalizePreparation($savePrepare, $aErrors, $oVariantMatching, $sIdentifier, $sCustomIdentifier, $aMatching) {
        $this->saveShopVariationAndPrimaryCategory($aMatching, $sIdentifier);

        $this->setAllErrorsAndPreparedStatus($aErrors);
        if (!empty($aErrors) || !$savePrepare) {
            // stay on prepare form
            return false;
        }

        if ($oVariantMatching !== null) {
            $this->saveToAttributesMatchingTable($oVariantMatching, $this->getAttributeIdentifier($sIdentifier), $sCustomIdentifier, $aMatching);
        }

        return true;
    }

    protected function triggerBeforeFinalizePrepareAction()
    {
        $aActions = $this->getRequest($this->sActionPrefix);
        $savePrepare = $aActions['prepareaction'] === '1';
        $this->oPrepareList->set('preparetype', $this->getSelectionNameValue());
        $this->setPreparedStatus(true);

        if ($this->prepareHasErrors($savePrepare)) {
            $this->setPreparedStatusToError();
            return false;
        }

        $aMatching = $this->getRequestField();

        $variationThemeData = $this->getVariationThemeValidationData($aMatching);
        $variationThemeAttributes = $variationThemeData['variationThemeAttributes'];
        $submittedVariationThemeCode = $variationThemeData['submittedVariationThemeCode'];

        $variationThemeExists = isset($aMatching['variationthemealldata']);
        if ($variationThemeExists) {
            // Save variation theme to prepare table and it will be later used for making addItems request(split & skip)
            $this->oPrepareList->set(
                'variation_theme',
                json_encode(array($submittedVariationThemeCode => $variationThemeAttributes),true)
            );
            unset($aMatching['variationthemecode']);
            unset($aMatching['variationthemealldata']);
        }

        $this->saveVariationThemeBlacklist($aMatching);
        $sIdentifier = $this->getIdentifier($aMatching);

        if (empty($sIdentifier)) {
            MLMessage::gi()->addError(MLI18n::gi()->get(self::getMPName() . '_prepareform_category'), null, !MLHttp::gi()->isAjax());

            $this->setPreparedStatus(false);

            return false;
        }

        $sCustomIdentifier = $this->getCustomIdentifier();

        if (isset($aMatching['variationgroups'])) {
            if (!empty($aMatching['variationgroups']['new'])) {
                $aMatching = $aMatching['variationgroups']['new'];
            } else if(!empty($aMatching['variationgroups'][$sIdentifier])) {
                $aMatching = $aMatching['variationgroups'][$sIdentifier];
            } else {
                $aMatching = $this->getMatchingFallback($aMatching['variationgroups']);

            }

            $oVariantMatching = $this->getVariationDb();
            unset($aMatching['variationgroups.code']);

            $aErrors = array();
            $previouslyMatchedAttributes = array();
            $emptyCustomName = false;
            $maxNumberOfAdditionalAttributes = $this->getNumberOfMaxAdditionalAttributes();
            $numberOfMatchedAdditionalAttributes = 0;
            
            foreach ($aMatching as $key => &$value) {
                if (isset($value['Required'])) {
                    // If value is required convert Required to boolean value.
                    $value['Required'] = in_array($value['Required'],array(1, true, '1', 'true'),true);
                }

                // Initial value for error is false.
                $value['Error'] = false;
                // Flag used for validating only those attributes for which save or delete button is pressed.
                $isSelectedAttribute = $key === $aActions['prepareaction'];


                $sAttributeName = $value['AttributeName'];
                // If variation theme is sent in request and submitted attribute is in attributes of variation theme
                // that is variation theme attribute for which validation should be the same as for required attribute.
                $isVariationThemeAttribute = $variationThemeExists && in_array($key, $variationThemeAttributes);

                if (!isset($value['Code'])) {
                    // this will happen only if attribute was matched and then it was deleted from the shop
                    $value['Code'] = '';
                }

                $this->setValidatedDataForRequiredOrVariationThemeAttribute(
                    $value,
                    $isVariationThemeAttribute,
                    $savePrepare,
                    $isSelectedAttribute,
                    $sAttributeName,
                    $aMatching,
                    $aErrors,
                    $key
                );

                // this field is only available on attributes that are FreeText Kind
                // this is used to improve auto matching if checked no matched values will be saved
                // we will use shop values and do the matching during product upload
                if (isset($value['UseShopValues']) && $value['UseShopValues'] === '1') {
                    $value['Values'] = array();
                } else {
                    $this->transformMatching($value);
                    $this->validateCustomAttributes($key, $value, $previouslyMatchedAttributes, $aErrors, $emptyCustomName,
                        $savePrepare, $isSelectedAttribute, $numberOfMatchedAdditionalAttributes);
                    $this->removeCustomAttributeHint($value);

                    // this field is only available on attributes that are FreeText Kind
                    // this is used to improve auto matching if checked no matched values will be saved
                    // we will use shop values and do the matching during product upload
                    if (isset($value['UseShopValues']) && $value['UseShopValues'] === '1') {
                        $value['Values'] = array();
                    } else {

                        if (!$this->attributeIsMatched($value) || !is_array($value['Values']) ||
                            !isset($value['Values']['FreeText'])
                        ) {
                            continue;
                        }

                        $sInfo = self::getMessage('_prepare_variations_manualy_matched');
                        $sFreeText = $value['Values']['FreeText'];
                        unset($value['Values']['FreeText']);
                        $isNoSelection = $value['Values']['0']['Shop']['Key'] === 'noselection'
                            || $value['Values']['0']['Marketplace']['Key'] === 'noselection';

                        $this->setValidatedNoSelectionAttribute(
                            $value,
                            $isVariationThemeAttribute,
                            $savePrepare,
                            $isSelectedAttribute,
                            $sAttributeName,
                            $aErrors
                        );

                        if ($isNoSelection) {
                            continue;
                        }

                        if ($value['Values']['0']['Marketplace']['Key'] === 'reset') {
                            $aMatching[$key]['Values'] = array();
                            continue;
                        }

                        // here is useful for first matching not updating matched value
                        if ($value['Values']['0']['Marketplace']['Key'] === 'manual') {
                            $sInfo = self::getMessage('_prepare_variations_free_text_add');
                            if (empty($sFreeText)) {
                                if ($this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)) {
                                    if ($savePrepare) {
                                        $aErrors = $this->setMissingFreetextAttributesError($key, $aErrors);
                                    }
                                    $value['Error'] = true;
                                }

                                unset($value['Values']['0']);
                                continue;
                            }

                            $value['Values']['0']['Marketplace']['Value'] = $sFreeText;
                        }

                        if ($value['Values']['0']['Marketplace']['Key'] === 'auto') {
                            $this->autoMatch($sIdentifier, $key, $value);
                            $value['Values'] = $this->fixAttributeValues($value['Values']);
                            // Validate if auto match didn't find any matching
                            if (empty($value['Values']) &&
                                $this->isRequiredAttribute($value, $isVariationThemeAttribute) &&
                                $this->shouldValidateAttribute($savePrepare, $isSelectedAttribute)
                            ) {
                                if ($savePrepare) {
                                    $aErrors = $this->setMissingRequiredAttrbiteError($sAttributeName, $aErrors);
                                }

                                $value['Error'] = true;
                            }
                            continue;
                        }

                        $this->checkNewMatchedCombination($value['Values']);
                        $this->setAttributeValues($value, $sInfo);
                    }
                }
            }
            
            if ($savePrepare && $numberOfMatchedAdditionalAttributes > $maxNumberOfAdditionalAttributes) {
                // If there is a limit on number of custom attributes, validation message should be displayed.
                $aErrors[] = self::getMessage('_prepare_variations_error_maximal_number_custom_attributes_exceeded',
                    array('numberOfAttributes' => $maxNumberOfAdditionalAttributes));
            }

            // If variation theme is defined for that category and mandatory but nothing is selected.
            if ($submittedVariationThemeCode === 'null') {
                $aErrors[] = self::getMessage('_prepare_variations_theme_mandatory_error');
            }

            $this->saveShopVariationAndPrimaryCategory($aMatching, $sIdentifier);

            $this->setAllErrorsAndPreparedStatus($aErrors);
            if (!empty($aErrors) || !$savePrepare) {
                // stay on prepare form
                return false;
            }

            $this->saveToAttributesMatchingTable($oVariantMatching, $this->getAttributeIdentifier($sIdentifier), $sCustomIdentifier, $aMatching);
            //MLMessage::gi()->addSuccess(self::getMessage('_prepare_match_variations_saved'));
        } else {// if nothing is matched in attribute matching we should save varaiationgroups as primary category of marketplace
            $this->oPrepareList->set(MLDatabase::getPrepareTableInstance()->getPrimaryCategoryFieldName(), $sIdentifier);
        }
        return true;
    }
    protected function getAttributeIdentifier($categoryID) {
        return $categoryID;
    }
    /**
     * Saves prepare attributes to AM table if it does not exist.
     *
     * @param ML_Database_Model_Table_VariantMatching_Abstract $oVariantMatching
     * @param string $sIdentifier
     * @param string $sCustomIdentifier
     * @param array $aMatching
     * @throws Exception
     */
    protected function saveToAttributesMatchingTable($oVariantMatching, $sIdentifier, $sCustomIdentifier, $aMatching) {
        $aShopVariation = $oVariantMatching
            ->set('Identifier', $sIdentifier)
            ->set('CustomIdentifier', $sCustomIdentifier)
            ->get('ShopVariation');

        if (!isset($aShopVariation)) {
            $oVariantMatching
                ->set('Identifier', $sIdentifier)
                ->set('CustomIdentifier', $sCustomIdentifier)
                ->set('ShopVariation', json_encode($aMatching))
                ->set('ModificationDate', date('Y-m-d H:i:s'))
                ->save();
        }
    }

    protected function fixAttributeValues($values) {
        if (isset($values['0']) && !empty($values['0']['Marketplace']['Info'])) {
            $fixedValues = array();
            $i = 1;
            foreach ($values as $value) {
                $fixedValues[$i] = $value;
                $i++;
            }

            return $fixedValues;
        }

        return $values;
    }

    protected function setPreparedStatus($verified, $productIDs = array()) {
        $status = $verified ? 'OK' : 'ERROR';

        if (!empty($productIDs)) {
            foreach ($productIDs as $key) {
                $prepareItem = $this->oPrepareList->getByKey('[' . $key . ']');
                if (isset($prepareItem)) {
                    $prepareItem->set('verified', $status);
                }
            }
        } else {
            $this->oPrepareList->set('verified', $status);
        }
    }

    public function triggerBeforeField(&$aField)
    {
        parent::triggerBeforeField($aField);
        $sName = $aField['realname'];
        if ($sName === 'variationgroups.value') {
            return;
        }
        $aRequestTriggerField = MLRequest::gi()->data('ajaxData');
        if (MLHttp::gi()->isAjax() && $aRequestTriggerField !== null) {
            if ($aRequestTriggerField['method'] === 'variationmatching') {
                unset($aField['value']);
                return;
            }
        }

        if (!isset($aField['value'])) {
            $mValue = null;
            $aRequestFields = $this->getRequestField();
            $aNames = explode('.', $aField['realname']);
            $value = null;
            if (count($aNames) > 1 && isset($aRequestFields[$aNames[0]])) {
                // parent real name is in format "variationgroups.qnvjagzvcm1hda____.rm9ybwf0.code"
                // and name in request is "[variationgroups][Buchformat][Format][Code]"
                $sName = $sKey = $aNames[0];
                $aTmp = $aRequestFields[$aNames[0]];
                $iMax = count($aNames);
                for ($i = 1;  $i < $iMax; $i++) {
                    if (is_array($aTmp)) {
                        foreach ($aTmp as $key => $value) {
                            if (strtolower($key) === 'code') {
                                break;
                            } elseif (strtolower($key) == $aNames[$i]) {
                                $sName .= '.' . $key;
                                $sKey = $key;
                                $aTmp = $value;
                                break;
                            }
                        }
                    } else {
                        break;
                    }
                }

                if (isset($sKey) && $sKey !== $aNames[0] && !is_array($value)) {
                    $mValue = array($sKey => $value, 'name' => $sName);
                }
            }

            if ($mValue != null) {
                $aField['value'] = reset($mValue);
                $aField['valuearr'] = $mValue;
            }
        }
    }

    public function triggerAfterField(&$aField, $parentCall = false)
    {
        //TODO Check this parent call
        parent::triggerAfterField($aField);

        if ($parentCall) {
            return;
        }

        $sName = $aField['realname'];

        // when top variation groups drop down is changed, its value is updated in getRequestValue
        // otherwise, it should remain empty.
        // without second condition this function will be executed recursevly because of the second line below.
        if (!isset($aField['value'])) {
            $sProductId = $this->getProductId();

            $oPrepareTable = MLDatabase::getPrepareTableInstance();

            $aPrimaryCategories = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());
            $sPrimaryCategoriesValue = isset($aPrimaryCategories['[' . $sProductId . ']'])
                ? $aPrimaryCategories['[' . $sProductId . ']'] : reset($aPrimaryCategories);

            if ($sName === 'variationgroups.value') {
                $aField['value'] = $sPrimaryCategoriesValue;
            } else {
                // check whether we're getting value for standard group or for custom variation mathing group
                $sCustomGroupName = $this->getField('variationgroups.value', 'value');
                $aCustomIdentifier = explode(':', $sCustomGroupName);

                if (count($aCustomIdentifier) == 2 && ($sName === 'attributename' || $sName === 'customidentifier')) {
                    $aField['value'] = $aCustomIdentifier[$sName === 'attributename' ? 0 : 1];
                    return;
                }

                $aNames = explode('.', $sName);
                if (count($aNames) == 4 && strtolower($aNames[3]) === 'code') {
                    $aValue = $this->getPreparedShopVariationForList($this->oPrepareList);
                    if (!isset($aValue) || strtolower($sPrimaryCategoriesValue) !== strtolower($aNames[1])) {
                        // real name is in format "variationgroups.qnvjagzvcm1hda____.rm9ybwf0.code"
                        $sCustomIdentifier = count($aCustomIdentifier) == 2 ? $aCustomIdentifier[1] : '';
                        if (empty($sCustomIdentifier)) {
                            $sCustomIdentifier = $this->getCustomIdentifier();
                        }
                        $aValue = $this->getAttributesFromDB($aNames[1], $sCustomIdentifier);
                    }

                    if ($aValue) {
                        foreach ($aValue as $sKey => &$aMatch) {
                            if (strtolower($sKey) === $aNames[2]) {
                                if (!isset($aMatch['Code'])) {
                                    // this will happen only if attribute was matched and then deleted from the shop
                                    $aMatch['Code'] = '';
                                }
                                $aField['value'] = $aMatch['Code'];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Detects if matched attribute is deleted on shop.
     * @param array $savedAttribute
     * @param string $warningMessageCode message code that should be displayed
     * @return bool
     */
    public function detectIfAttributeIsDeletedOnShop($savedAttribute, &$warningMessageCode) {
        return MLFormHelper::getPrepareAMCommonInstance()->detectIfAttributeIsDeletedOnShop($savedAttribute, $warningMessageCode);
    }

    protected function variationGroupsField(&$aField)
    {
        $aField['subfields']['variationgroups.value']['values'] = array('' => '..') + $this->getPrimaryCategoryFieldValues();

        foreach ($aField['subfields'] as &$aSubField) {
            //adding current cat, if not in top cat
            if (!array_key_exists((string)$aSubField['value'], $aSubField['values'])) {
                $oCat = MLDatabase::factory(self::getMPName().'_categories'.$aSubField['cattype']);
                $oCat->init(true)->set('categoryid', $aSubField['value'] ? $aSubField['value'] : 0);
                $sCat = '';
                foreach ($oCat->getCategoryPath() as $oParentCat) {
                    $sCat = $oParentCat->get('categoryname').' &gt; '.$sCat;
                }
                if (empty($sCat)) {
                    $aSubField['values'][$aSubField['value']] = MLI18n::gi()->{'ml_prepare_form_category_notvalid'};
                } else {
                    $aSubField['values'][$aSubField['value']] = substr($sCat, 0, -6);
                }
            }
        }
    }

    protected function variationMatchingField(&$aField)
    {
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
            'field' => array(
                'type' => 'switch',
            ),
        );
    }

    protected function variationGroups_ValueField(&$aField)
    {
        $aField['type'] = 'categoryselect';
        $aField['cattype'] = 'marketplace';
    }

    protected function getPrimaryCategoryFieldValues()
    {
        return ML::gi()->instance('controller_' . self::getMPName() . '_config_prepare')
            ->getField('primarycategory', 'values');
    }

    protected function callGetCategoryDetails($sCategoryId) {
        return MLFormHelper::getPrepareAMCommonInstance()->getCategoryDetails($sCategoryId);
    }

    /**
     * Serialized data for variation pattern(variation theme) will be submitted through the hidden field.
     * @param $aField
     */
    protected function variationThemeAllDataField(&$aField)
    {
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
            'field' => array (
                'type' => 'hidden',
                'value' => '',
            ),
        );

        $mParentValue = $this->getField('variationgroups.value', 'value');
        if ($mParentValue != '') {
            $categoryDetails = $this->callGetCategoryDetails($mParentValue);

            if (!empty($categoryDetails['DATA']['variation_details'])) {
                $aField['ajax']['field']['value'] = htmlspecialchars(json_encode($categoryDetails['DATA']['variation_details']));
                $aField['value'] = htmlspecialchars(json_encode($categoryDetails['DATA']['variation_details']));
            }
        }
    }

    /**
     * Serialized data for variation blacklist will be submitted through the hidden field.
     * @param $aField
     */
    protected function variationThemeBlacklistField(&$aField)
    {
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '.variationMatchingSelector .input select',
            'trigger' => 'change',
            'field' => array(
                'type' => 'hidden',
                'value' => '',
            )
        );

        $mParentValue = $this->getField('variationgroups.value', 'value');
        if (!empty($mParentValue)) {
            $categoryDetails = $this->callGetCategoryDetails($mParentValue);

            if (!empty($categoryDetails['DATA']['variation_details_blacklist'])) {
                $aField['ajax']['field']['value'] = htmlspecialchars(json_encode($categoryDetails['DATA']['variation_details_blacklist']));
            }
        }
    }

    /**
     * For all marketplaces that have variation pattern(variation theme) select with options from marketplace
     * will be displayed.
     * @param $aField
     */
    protected function variationThemeCodeField(&$aField)
    {
        // Helper for php8 compatibility - can't pass null to htmlspecialchars_decode 
        $sValue = MLHelper::gi('php8compatibility')->checkNull($this->getField('variationthemealldata', 'value'));
        $variationThemes = json_decode(htmlspecialchars_decode($sValue), true);
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' . $this->getField('variationgroups.value', 'id'),
            'trigger' => 'change',
        );

        $mParentValue = $this->getField('variationgroups.value', 'value');

        if (is_array($variationThemes) && count($variationThemes) > 0 && $mParentValue != '') {

                $variationThemeNames = array();
                foreach ($variationThemes as $variationThemeKey => $variationTheme) {
                    $variationThemeNames[$variationThemeKey] = $variationTheme['name'];
                }

                $aField['values'] = array('null' => $this->__('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT')) + $variationThemeNames;
                $primaryCategory = $this->oPrepareList->get('PrimaryCategory');
                $differentCategory = $mParentValue !== array_pop($primaryCategory);
                $savedVariationThemes = $differentCategory ? array() : $this->oPrepareList->get('variation_theme');

                $savedVariationTheme = array_pop($savedVariationThemes);
                if (empty($savedVariationTheme)) {
                    $savedVariationTheme = array('null' => array());
                }

                $savedVariationThemeCode = key($savedVariationTheme);

                // Value of an ajax field in V3 an array. That array has format :
                // $aField['value'] = array($codeOfDependingField => $variationThemeCode);
                $aField['value'] = array($mParentValue => $savedVariationThemeCode);
                $aField['ajax']['field']['type'] = 'dependonfield';
                $aField['dependonfield']['depend'] = 'variationgroups.value';
                $aField['dependonfield']['field']['type'] = 'select';
                
                // Add error styling if variation theme validation failed
                if ($this->hasVariationThemeError()) {
                    $aField['ajax']['field']['cssclasses'] = array('ml-error');
                    $aField['labelErrorClass'] = 'ml-error-label';
                }
            }
    }

    public function getMPVariationAttributes($sVariationValue) {
        if ($this->aMPAttributes !== null) {
            return $this->aMPAttributes;
        }

        $aValues = $this->callGetCategoryDetails($sVariationValue);
        $result = array();
        if ($aValues && !empty($aValues['DATA']['attributes'])) {
            foreach ($aValues['DATA']['attributes'] as $key => $value) {
                $result[$key] = array(
                    'value' => $value['title'],
                    'required' => isset($value['mandatory']) ? $value['mandatory'] : true,
                    'changed' => isset($value['changed']) ? $value['changed'] : null,
                    'desc' => isset($value['desc']) ? $value['desc'] : '',
                    'values' => !empty($value['values']) ? $value['values'] : array(),
                    'dataType' => !empty($value['type']) ? $value['type'] : 'text',
                    'categoryId' => !empty($value['categoryId']) ? $value['categoryId'] : null,
                    'attributeId' => !empty($value['id']) ? $value['id'] : null,
                );
            }
        }

        $aResultFromDB = $this->getPreparedData($sVariationValue, '');

        if (!is_array($aResultFromDB)) {
            $aResultFromDB = $this->getAttributesFromDB($sVariationValue);
        }

        if ($this->getNumberOfMaxAdditionalAttributes() > 0) {
            $additionalAttributes = array();
            $newAdditionalAttributeIndex = 0;
            $positionOfIndexInAdditionalAttribute = 2;

            if (!empty($aResultFromDB)) {
                foreach ($aResultFromDB as $key => $value) {
                    if (strpos($key, 'additional_attribute_') === 0) {
                        $additionalAttributes[$key] = $value;
                        $additionalAttributeIndex = explode('_', $key);
                        $additionalAttributeIndex = (int)$additionalAttributeIndex[$positionOfIndexInAdditionalAttribute];
                        $newAdditionalAttributeIndex = ($newAdditionalAttributeIndex > $additionalAttributeIndex) ?
                            $newAdditionalAttributeIndex + 1 : $additionalAttributeIndex + 1;
                    }
                }
            }

            $additionalAttributes['additional_attribute_' . $newAdditionalAttributeIndex] = array();

            foreach ($additionalAttributes as $attributeKey => $attributeValue) {
                $result[$attributeKey] = array(
                    'value' => self::getMessage('_prepare_variations_additional_attribute_label'),
                    'custom' => true,
                    'required' => false,
                );
            }
        }

        $this->detectChanges($result, $sVariationValue);

        $this->aMPAttributes = $result;
        return $this->aMPAttributes;
    }

    /**
     * @param $sIdentifier
     * @param $sCustomIdentifier
     * @return mixed
     */
    protected function getPreparedData($sIdentifier, $sCustomIdentifier)
    {
        $sProductId = $this->getProductId();

        $oPrepareTable = MLDatabase::getPrepareTableInstance();
        $sPrimaryCategory = $this->oPrepareList->get($oPrepareTable->getPrimaryCategoryFieldName());

        $sPrimaryCategoryValue = isset($sPrimaryCategory['[' . $sProductId . ']'])
            ? $sPrimaryCategory['[' . $sProductId . ']'] : reset($sPrimaryCategory);

        if (!empty($sPrimaryCategory)) {
            if ($sPrimaryCategoryValue === $sIdentifier) {
                $aValue = $this->getPreparedShopVariationForList($this->oPrepareList);
            }
        }

        if (!isset($aValue)) {
            $aValue = $this->getAttributesFromDB($sIdentifier, $sCustomIdentifier);
        }

        return $aValue;
    }

    /**
     * Gets ShopVariation data fof given prepare list and current product
     *
     * @param ML_Database_Model_list $oPrepareList Where to look for ShopVariation field data
     *
     * @param bool $setDefaultValue If set to true in case when exact match by product id is not found
     * first value from the list will be returned. Set this to false to get only exact product id match
     *
     * @return mixed|null ShopVariation field data or null if nothing is found for current product
     */
    protected function getPreparedShopVariationForList($oPrepareList, $setDefaultValue = true)
    {
        $sProductId = $this->getProductId();
        $aValue = null;

        $aShopVariation = $oPrepareList->get(MLDatabase::getPrepareTableInstance()->getShopVariationFieldName());
        if (!empty($aShopVariation) && isset($aShopVariation['[' . $sProductId . ']'])) {
            $aValue = $aShopVariation['[' . $sProductId . ']'];
        } else if (!empty($aShopVariation) && $setDefaultValue) {
            $aValue = reset($aShopVariation);
        }

        return $aValue;
    }

    protected function getAttributeValues($sIdentifier, $sCustomIdentifier, $sAttributeCode = null, $bFreeText = false)
    {
        $aValue = $this->getPreparedData($sIdentifier, $sCustomIdentifier);
        if ($aValue) {
            if ($sAttributeCode !== null) {
                foreach ($aValue as $sKey => $aMatch) {
                    if ($sKey === $sAttributeCode) {
                        return isset($aMatch['Values']) ? $aMatch['Values'] : ($bFreeText ? '' : array());
                    }
                }
            } else {
                return $aValue;
            }
        }

        if ($bFreeText) {
            return '';
        }

        return array();
    }

    protected function getUseShopValues($sIdentifier, $sCustomIdentifier, $sAttributeCode = null) {
        $aValue = $this->getPreparedData($sIdentifier, $sCustomIdentifier);
        $result = null;
        if (is_array($aValue)) {
            if ($sAttributeCode !== null) {
                foreach ($aValue as $sKey => $aMatch) {
                    if ($sKey === $sAttributeCode && isset($aMatch['UseShopValues'])) {
                        $result = $aMatch['UseShopValues'];
                        break;
                    }
                }
            }
        }

        return $result;
    }

    protected function getShopAttributes()
    {
        if ($this->shopAttributes == null) {
            $this->shopAttributes = MLFormHelper::getPrepareAMCommonInstance()->getSortedShopAttributes();
        }

        return $this->shopAttributes;
    }

    protected function getShopAttributeValues($sAttributeCode) {
        return MLFormHelper::getPrepareAMCommonInstance()->getShopAttributeValues($sAttributeCode);
    }
    
    protected function getMPAttributeValues($sCategoryId, $sMpAttributeCode, $sAttributeCode = false) {
        $response = $this->callGetCategoryDetails($sCategoryId);
        $fromMP = false;
        $sType = '';
        foreach ($response['DATA']['attributes'] as $key => $attribute) {
            if ($key === $sMpAttributeCode && !empty($attribute['values'])) {
                $aValues = $attribute['values'];
                $sType = $attribute['type']; 
                $fromMP = true;
                break;
            }
        }

        if (!isset($aValues)) {
            if ($sAttributeCode) {
                $shopValues = $this->getShopAttributeValues($sAttributeCode);
                foreach ($shopValues as $value) {
                    $aValues[$value] = $value;
                }
            } else {
                $aValues = array();
            }
        } else if (    $sAttributeCode
                    && (    $sType == 'text'
                         || $sType == 'selectAndText'
                         || $sType == 'multiSelectAndText')) {
                // predefined values exist, but free text is allowed => add shop's values to selection
                // at the end, and sorted, so that it's visible that it's added
                $shopValues = $this->getShopAttributeValues($sAttributeCode);
                asort($shopValues);
                $aLowerValues = array_map('mb_strtolower', $aValues);
                foreach ($shopValues as $value) {
                    if (array_search(mb_strtolower($value), $aLowerValues) !== false) {
                        continue;
                    }
                    $aValues[$value] = $value;
                }
        }

        return array(
            'values' => isset($aValues) ? $aValues : array(),
            'from_mp' => $fromMP
        );
    }

    /**
     * get attribute matching from attribute matching table e.g. magnalister_amazon_variationmatching
     * @param $sIdentifier
     * @param $sCustomIdentifier
     * @return array|mixed|null
     */
    protected function getAttributesFromDB($sIdentifier, $sCustomIdentifier = '') {
        if ($sCustomIdentifier === null) {
            $sCustomIdentifier = '';
        }

        $hashParams = md5($sIdentifier.$sCustomIdentifier.'ShopVariation');
        if (!array_key_exists($hashParams, $this->variationCache)) {
            $this->variationCache[$hashParams] = $this->getVariationDb()
                ->set('Identifier', $sIdentifier)
                ->set('CustomIdentifier', $sCustomIdentifier)
                ->get('ShopVariation');
        }

        if ($this->variationCache[$hashParams]) {
            return $this->variationCache[$hashParams];
        }

        return array();
    }

    protected function getErrorValue($sIdentifier, $sCustomIdentifier, $sAttributeCode)
    {
        $aValue = $this->oPrepareList->get('shopvariation');
        $sProductId = $this->getProductId();

        if (!empty($aValue['[' . $sProductId . ']']) && is_array($aValue['[' . $sProductId . ']'])) {
            foreach ($aValue['[' . $sProductId . ']'] as $sKey => $aMatch) {
                if ($sKey === $sAttributeCode) {
                    return $aMatch['Error'];
                }
            }
        }

        return false;
    }

    protected function callApi($actionName, $aData = array(), $iLifeTime = 60)
    {
        try {
            $aResponse = MagnaConnector::gi()->submitRequestCached(array('ACTION' => $actionName, 'DATA' => $aData), $iLifeTime);
            if ($aResponse['STATUS'] == 'SUCCESS' && isset($aResponse['DATA']) && is_array($aResponse['DATA'])) {
                return $aResponse['DATA'];
            }
        } catch (MagnaException $e) {

        }

        return array();
    }

    /**
     * @return ML_Database_Model_Table_VariantMatching_Abstract
     */
    protected function getVariationDb()
    {
        return MLDatabase::getVariantMatchingTableInstance();
    }

    protected function autoMatch($categoryId, $sMpAttributeCode, &$aAttributes)
    {
        $aMPAttributeValues = $this->getMPAttributeValues($categoryId, $sMpAttributeCode, $aAttributes['Code']);
        $sInfo = self::getMessage('_prepare_variations_auto_matched');
        $blFound = false;
        if ($aAttributes['Values']['0']['Shop']['Key'] === 'all') {
            $newValue = array();
            $i = 0;
            foreach ($this->getShopAttributeValues($aAttributes['Code']) as $keyAttribute => $valueAttribute) {
                $blFoundInMP = false;
                foreach ($aMPAttributeValues['values'] as $key => $value) {
                    if (strcasecmp($valueAttribute, $value) == 0) {
                        $newValue[$i]['Shop']['Key'] = $keyAttribute;
                        $newValue[$i]['Shop']['Value'] = $valueAttribute;
                        $newValue[$i]['Marketplace']['Key'] = $key;
                        $newValue[$i]['Marketplace']['Value'] = $key;
                        // $value can be array if it is multi value, so that`s why this is checked
                        // and converted to string if it is. That is done because this information will be displayed in matched
                        // table.
                        $newValue[$i]['Marketplace']['Info'] = (is_array($value) ? implode(', ', $value) : $value) . $sInfo;
                        $blFound = $blFoundInMP = true;
                        $i++;
                        break;
                    } 
                }
                // if value is not found in mp values and if attribute can be added as freetext, it is added here as freetext
                if(!$blFoundInMP && isset($aAttributes['DataType']) && strpos(strtolower($aAttributes['DataType']), 'text') !== false ) {
                    $newValue[$i]['Shop']['Key'] = $keyAttribute;
                    $newValue[$i]['Shop']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Key'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Info'] = $valueAttribute. self::getMessage('_prepare_variations_free_text_add');
                    $blFound = true;
                    $i++;
                }
            }

            $aAttributes['Values'] = $newValue;
        } else {
            foreach ($aMPAttributeValues['values'] as $key => $value) {
                if (strcasecmp($aAttributes['Values']['0']['Shop']['Value'], $value) == 0) {
                    $aAttributes['Values']['0']['Marketplace']['Key'] = $key;
                    $aAttributes['Values']['0']['Marketplace']['Value'] = $key;
                    // $value can be array if it is multi value, so that`s why this is checked
                    // and converted to string if it is. That is done because this information will be displayed in matched
                    // table.
                    $aAttributes['Values']['0']['Marketplace']['Info'] =
                        (is_array($value) ? implode(', ', $value) : $value) . $sInfo;
                    $blFound = true;
                    break;
                }
            }
        }

        if (!$blFound) {
            unset($aAttributes['Values']['0']);
        }

        $this->checkNewMatchedCombination($aAttributes['Values']);
    }

    protected function checkNewMatchedCombination(&$aAttributes)
    {
        foreach ($aAttributes as $key => $value) {
            if ($key === 0) {
                continue;
            }

            if (isset($aAttributes['0']) && $value['Shop']['Key'] === $aAttributes['0']['Shop']['Key']) {
                unset($aAttributes[$key]);
                break;
            }
        }
    }

    /**
     * Checks for each attribute whether it is prepared differently in Attributes Matching tab,
     * and if so, marks it Modified.
     * Arrays cannot be compared directly because values could be in different order (with different numeric keys).
     *
     * @param $result
     * @param $sIdentifier
     */
    protected function detectChanges(&$result, $sIdentifier) {
        // similar validation exists in ML_Productlist_Model_ProductList_Abstract::isPreparedDifferently
        $globalMatching = MLDatabase::getVariantMatchingTableInstance()->getMatchedVariations($sIdentifier, $this->getCustomIdentifier());

        $oPrepareTable = MLDatabase::getPrepareTableInstance();

        $sShopVariationField = $oPrepareTable->getShopVariationFieldName();
        $sProductId = $this->getProductId();

        $oPrepareTable->set($oPrepareTable->getProductIdFieldName(), $sProductId);
        $mainCategory = $oPrepareTable->get($oPrepareTable->getPrimaryCategoryFieldName());

        if ($mainCategory !== $sIdentifier) {
            return;
        }

        $productMatching = $oPrepareTable
            ->set($oPrepareTable->getPrimaryCategoryFieldName(), $sIdentifier)
            ->get($sShopVariationField);


        if (is_array($globalMatching)) {
            foreach ($globalMatching as $attributeCode => $attributeSettings) {
                // If attribute is deleted on MP do not detect changes for that attribute at all since whole attribute is missing!
                if (!isset($result[$attributeCode])) {
                    continue;
                }

                // attribute is matched globally but not on product
                if ($productMatching !== 'null' && $productMatching !== null && empty($productMatching[$attributeCode])) {
                    $result[$attributeCode]['modified'] = true;
                    continue;
                }

                if (empty($productMatching)) {
                    continue;
                }

                $productAttrs = $productMatching[$attributeCode];

                if (!array_key_exists('Values', $productAttrs) || !array_key_exists('Values', $attributeSettings)) {
                    continue;
                }

                if (!is_array($productAttrs['Values']) || !is_array($attributeSettings['Values'])) {
                    $result[$attributeCode]['modified'] = $productAttrs != $attributeSettings;
                    continue;
                }

                $productAttrsValues = $productAttrs['Values'];
                $attributeSettingsValues = $attributeSettings['Values'];
                unset($productAttrs['Values']);
                unset($attributeSettings['Values']);

                // first compare without values (optimization)
                $allValuesMatched = count($productAttrsValues) === count($attributeSettingsValues);
                if ($productAttrs['Code'] == $attributeSettings['Code'] && $allValuesMatched) {
                    // compare values
                    // values could be in different order so we need to iterate through array and check one by one
                    foreach ($productAttrsValues as $attribute) {
                        // Since $productAttrsValues can be array of (string) values, we must check for existence of Info to
                        // avoid Fatal error: Cannot unset string offsets
                        if (!empty($attribute['Marketplace']['Info'])) {
                            unset($attribute['Marketplace']['Info']);
                        }

                        $found = false;
                        foreach ($attributeSettingsValues as $value) {
                            if (!empty($value['Marketplace']['Info'])) {
                                unset($value['Marketplace']['Info']);
                            }

                            if ($attribute == $value) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $allValuesMatched = false;
                            break;
                        }
                    }
                }

                $result[$attributeCode]['modified'] = !$allValuesMatched;
            }
        }
    }

    /**
     * Gets all data for marketplace attribute which is supplied.
     * @param $categoryId
     * @param $mpAttributeCode
     * @param $shopAttributeCode
     * @return array
     */
    public function getMPAttributes($categoryId, $mpAttributeCode, $shopAttributeCode)
    {
        $mpValues = $this->callGetCategoryDetails($categoryId);

        $valuesAndFromMp = $this->getMPAttributeValues($categoryId, $mpAttributeCode, $shopAttributeCode);
        $result = array(
            'values' => $valuesAndFromMp['values'],
            'from_mp' => $valuesAndFromMp['from_mp'],
        );

        if (isset($mpValues['DATA']) && isset($mpValues['DATA']['attributes'][$mpAttributeCode])) {
            $mpAttribute = $mpValues['DATA']['attributes'][$mpAttributeCode];
            $result = array_merge($result, array(
                    'value' => $mpAttribute['title'],
                    'required' => isset($mpAttribute['mandatory']) ? $mpAttribute['mandatory'] : true,
                    'changed' => isset($mpAttribute['changed']) ? $mpAttribute['changed'] : null,
                    'desc' => isset($mpAttribute['desc']) ? $mpAttribute['desc'] : '',
                    'dataType' => !empty($mpAttribute['type']) ? $mpAttribute['type'] : 'text',
                    'limit' => !empty($mpAttribute['limit']) ? $mpAttribute['limit'] : null,
                )
            );
        } else {
            $result['dataType'] = 'text';
        }

        return $result;
    }

    protected function getShopAttributeDetails($sAttributeCode)
    {
        return array(
            'values' => $this->getShopAttributeValues($sAttributeCode),
            'attributeDetails' => MLFormHelper::getShopInstance()->getFlatShopAttributesForMatching($sAttributeCode),
        );
    }

    /**
     * In case that multiple values are sent for shop and marketplace, that information will be json_encoded array.
     * Deserialization is done so that it can be properly saved to database.
     * @param $matchedAttribute
     */
    protected function transformMatching(&$matchedAttribute)
    {
        if (isset($matchedAttribute['Values']) && is_array($matchedAttribute['Values'])) {
            $emptyOptionValue = 'noselection';
            $multiSelectKey = 'multiselect';

            foreach ($matchedAttribute['Values'] as &$matchedAttributeValue) {
                if (is_array($matchedAttributeValue)) {
                    if (is_array($matchedAttributeValue['Shop']['Key'])) {
                        $matchedAttributeValue['Shop']['Value'] =
                            json_decode($matchedAttributeValue['Shop']['Value'], true);

                    } else if (strtolower($matchedAttributeValue['Shop']['Key']) === $multiSelectKey){
                        // If multi select is chosen but nothing is selected from multiple select, this value should be ignored.
                        $matchedAttributeValue['Shop']['Key'] = $emptyOptionValue;
                    }

                    if (is_array($matchedAttributeValue['Marketplace']['Key'])) {
                        $matchedAttributeValue['Marketplace']['Value'] =
                            json_decode($matchedAttributeValue['Marketplace']['Value'], true);

                    } else if (strtolower($matchedAttributeValue['Marketplace']['Key']) === $multiSelectKey) {
                        // If multi select is chosen but nothing is selected from multiple select, this value should be ignored.
                        $matchedAttributeValue['Marketplace']['Key'] = $emptyOptionValue;
                    }
                }
            }
        }
    }

    protected function getProductId()
    {
        if (isset($this->oProduct)) {
            $aVariations = $this->oProduct->getVariants();
            if (isset($aVariations) && count($aVariations) > 1) {
                return $aVariations[0]->get('id');
            }

            return $sProductId = $this->oProduct->get('id');
        }

        return null;
    }

    protected static function getMessage($sIdentifier, $aReplace = array())
    {
        return MLI18n::gi()->get(MLModule::gi()->getMarketPlaceName() . $sIdentifier, $aReplace);
    }

    protected static function getMPName()
    {
        return MLModule::gi()->getMarketPlaceName();
    }

    public function getManipulateMarketplaceAttributeValues($values) {
        return MLFormHelper::getPrepareAMCommonInstance()->getManipulateMarketplaceAttributeValues($values);
    }

    /**
     * Only for eBay and Hood
     * @return ML_Ebay_Model_Service_AddItems|ML_Amazon_Model_Service_AddItems|ML_Hood_Model_Service_AddItems
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     */
    protected function verifyItemByMarketplace() {
        list($oProduct, $oProductList) = $this->getFirstSelectedProduct();

        /* @var $oService ML_Ebay_Model_Service_AddItems|ML_Amazon_Model_Service_AddItems */
        $oService = MLService::getAddItemsInstance()->setValidationMode(true)->setProductList($oProductList);
        $cacheKey = MLModule::gi()->getMarketPlaceName(false).'_' . MLModule::gi()->getMarketPlaceId() . '_VerifyAddItems_Error';
        if (MLRequest::gi()->data('offset') == 0) {
            MLCache::gi()->delete($cacheKey);
            try {
                $oService->execute();
                MLCache::gi()->set($cacheKey, false);
            } catch (Exception $oEx) {
                MLMessage::gi()->addDebug(__FUNCTION__.'::'.__LINE__.': exception', ['exception' => $oEx]);
                $this->handleMarketplaceSpecificError($oEx, $oProduct, $oService);
                MLCache::gi()->set($cacheKey, $oService->getFirstError());
            }

        } else {
            $error = MLCache::gi()->get($cacheKey);
            if ($error) {
                $oService->addError($error);
            }
        }

        if ($oService->haveError()) {
            $this->oPrepareList->set('verified', 'ERROR');
        } else {
            $this->oPrepareList->set('verified', 'OK');
        }
        return $oService;
    }

    /**
     * Only for eBay and Hood
     * @return ML_Productlist_Model_ProductList_ShopAbstract
     * @throws Exception
     */
    protected function getFirstSelectedProduct() {
        $iParentId = null;
        foreach ($this->oSelectList->getList() as $oVariant) {
            $sProductsId = $oVariant->get('pid');
            $oProduct = MLProduct::factory()->set('id', $sProductsId);
            if (
                $iParentId !== null &&
                $iParentId != $oProduct->get('parentid')
            ) {
                break;
            }
            $oProductList = MLProductList::gi('generic')->addVariant($oProduct);
            $iParentId = $oProduct->get('parentid');
        }
        return array($oProduct, $oProductList);
    }


    /**
     * @param Exception $oEx
     * @param $oProduct ML_Shop_Model_Product_Abstract
     * @param $oService ML_Ebay_Model_Service_AddItems
     * @return void
     * @throws MLAbstract_Exception
     */
    protected function handleMarketplaceSpecificError($oEx, $oProduct, $oService) {

    }
    protected function getExtraFieldset($mParentValue) {

    }

    /**
     * Implement the function in marketplace to add fields to extra field set for attributes matching
     *
     * @param $aSubfield
     * @param $aSubfieldExtra
     * @param $aAjaxField
     * @return void
     */
    protected function populateExtraFieldsetFields($aSubfield, $aSubfieldExtra, $aAjaxField) {

    }

    protected function getExtraFieldsetView($aExtraFieldsetOptional) {

    }

    /**
     * Implement the function in marketplace to get the type of the values for extra field set in attributes matching
     *
     */
    protected function getExtraFieldsetType() {
        return null;
    }

    protected function isAttributeExtra($key) {
        return false;
    }

    protected function getMatchingFallback($variationGroups) {
    }

    protected function setMissingFreetextAttributesError($key, array $aErrors) {
        $aErrors[] = $key . self::getMessage('_prepare_variations_error_free_text');
        return $aErrors;
    }

    protected function setMissingRequiredAttrbiteError($sAttributeName, array $aErrors) {
        $aErrors[] = self::getMessage('_prepare_variations_error_text', array('attribute_name' => $sAttributeName));
        return $aErrors;
    }

    /**
     * Check if there's a variation theme error by looking for the specific error message
     * @return bool
     */
    protected function hasVariationThemeError() {
        $messages = MLMessage::gi()->getAll();
        $variationThemeErrorMessage = self::getMessage('_prepare_variations_theme_mandatory_error');

        //6 is index errors in messages
        if (!empty($messages[6])) {
            foreach ($messages[6] as $errorMessage) {
                if (strpos($errorMessage['message'], $variationThemeErrorMessage) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function getVariationGroup() {
        return MLFormHelper::getPrepareAMCommonInstance()->getVariationGroup(
            $this->getField('variationgroups.value', 'value'),
            $this->getField('attributename', 'value')
        );
    }

    function initializeShopAttributeSelections($aShopAttributes) {
        return MLFormHelper::getPrepareAMCommonInstance()->initializeShopAttributeSelections($aShopAttributes);
    }
}
