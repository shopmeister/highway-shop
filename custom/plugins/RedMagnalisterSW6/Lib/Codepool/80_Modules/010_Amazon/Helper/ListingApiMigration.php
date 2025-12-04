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

class ML_Amazon_Helper_ListingApiMigration {

    /**
     * Retrieves a list of products to migrate based on the given marketplace ID, limit, and offset.
     *
     * @param string $sMpId The marketplace ID used to filter products.
     * @param int $limit The maximum number of products to retrieve.
     * @param int $offset The offset for paginated results.
     *
     * @return array An array of products to migrate, containing properties such as ProductsID, ShopVariation, MainCategory, ProductType, and TopProductType.
     */
    public function getProductsToMigrate($sMpId, $limit) {
        return MLDatabase::getDbInstance()->fetchArray("
            SELECT ProductsID, ShopVariation, MainCategory, ProductType, BrowseNodes, TopProductType
              FROM magnalister_amazon_prepare
             WHERE      PrepareType = 'apply'
                    AND IsComplete = 'false'
                    AND mpID = '".MLDatabase::getDbInstance()->escape($sMpId)."'
             LIMIT ".MLDatabase::getDbInstance()->escape($limit)."
        ", true);
    }

    /**
     * Migrates the product by processing and mapping its attributes to a new structure
     * based on the product type and listing category details.
     *
     * @param array $aProduct An associative array containing product details,
     *                        including the product ID and ShopVariation data.
     * @return void
     * @throws Exception
     */
    public function migrateProduct($aProduct) {
        $sProductType = $this->getProductType($aProduct);
        if (!$sProductType) {
            $this->updateProductData([
                'IsComplete' => 'migration'
            ], $aProduct['ProductsID']);

            return;
        }

        $browseNode = $this->migrateBrowseNode($aProduct['BrowseNodes'], $this->callGetBrowseNodes($sProductType));
        $updateData =[
            'MainCategory' => $sProductType,
            'BrowseNodes' => json_encode([
                $browseNode,
            ]),
        ];
        if(!empty($browseNode)){
            $updateData['TopBrowseNode1'] = $browseNode;
        }
        // Update ProductType in the database
        $this->updateProductData($updateData, $aProduct['ProductsID']);

        $aNewAttributes = $this->callGetListingCategoryDetails($sProductType);
        $aAttributesMapping = $this->callGetAttributesMigrationMap();
        $aOldAttributes = json_decode($aProduct['ShopVariation'], true);

        $this->processAndMigrateAttributes($aProduct['ProductsID'], $aNewAttributes, $aOldAttributes, $aAttributesMapping, $sProductType);
    }

    /**
     * Determines and returns the product type by processing the provided product data.
     * The method decodes the product type data, ensures it exists, validates it against the main category,
     * and verifies its compatibility with the new product types listing from the API. If no matching product
     * type is found or data is invalid, the method returns false.
     *
     * @param array $aProduct Array representing the product, expected to contain keys 'ProductType' and 'MainCategory'.
     * @return string|false Returns the determined product type as a string if successful, or false if the product type
     *                      could not be determined or does not exist in the current product types list.
     */
    private function getProductType($aProduct){
        $aProductType = json_decode($aProduct['ProductType'], true);
        $isProductTypeSet = json_last_error() == JSON_ERROR_NONE;

        if ($isProductTypeSet
            && isset($aProduct['MainCategory'])
            && array_key_exists($aProduct['MainCategory'], $aProductType)
            && is_string($aProductType[$aProduct['MainCategory']])
        ) {
            $sProductType = mb_strtolower($aProductType[$aProduct['MainCategory']]);
            if (substr($sProductType, -4) == 'misc') {
                $sProductType = substr($sProductType, 0, -4);
            }
            
            // Combine MainCategory with ProductType for matching
            $sCombinedProductType = mb_strtolower($aProduct['MainCategory']) . $sProductType;
        } else {
            //skip the product in case there is no category defined
            return false;
        }

        $aNewProductTypes = $this->callGetListingProductTypes();
        
        // First try to match the combined product type
        if (array_key_exists($sCombinedProductType, $aNewProductTypes)) {
            return $aNewProductTypes[$sCombinedProductType];
        }
        
        // If no direct match, try original product type
        if (array_key_exists($sProductType, $aNewProductTypes)) {
            return $aNewProductTypes[$sProductType];
        }
        
        // Try removing 's' from combined product type
        if (substr($sCombinedProductType, -1) == 's') {
            $sCombinedProductTypeSingular = substr($sCombinedProductType, 0, -1);
            if (array_key_exists($sCombinedProductTypeSingular, $aNewProductTypes)) {
                return $aNewProductTypes[$sCombinedProductTypeSingular];
            }
        }
        
        // Try removing 's' from original product type
        if (substr($sProductType, -1) == 's') {
            $sProductTypeSingular = substr($sProductType, 0, -1);
            if (array_key_exists($sProductTypeSingular, $aNewProductTypes)) {
                return $aNewProductTypes[$sProductTypeSingular];
            }
        }
        
        //skip the product in case the product type is not found in the new listing api
        return false;
    }

    /**
     * Processes and stores the given product attributes by mapping new attributes
     * with old ones based on the provided attributes mapping and product type.
     *
     * @param int $productId The ID of the product for which attributes are being stored.
     * @param array $newAttributes The new attributes fetched for the product.
     * @param array $oldAttributes The existing attributes of the product.
     * @param array $attributesMapping A mapping array to correlate old attributes with new ones.
     * @param string $sProductType The product type identifier used for attribute processing.
     * @return void
     * @throws Exception If the new attributes array is empty.
     */
    private function processAndMigrateAttributes($productId, $newAttributes, $oldAttributes, $attributesMapping, $sProductType) {
        $storeAttributes = array();
        if (empty($newAttributes)) {
            MLMessage::gi()->addError('The GetCategoriesDetails request is empty');
            throw new Exception('The GetCategoriesDetails request is empty');
        }

        foreach($oldAttributes as $name => $data) {
            $snakeCaseName = $this->convertStringToSnakeCase($name);
            $attributeValues = $this->extractAttributeValuesFromData($data);
            if (!array_key_exists($snakeCaseName, $attributesMapping) && !array_key_exists($name.'__value', $newAttributes)) {
                $storeAttributes[$name] = $data;
                continue;
            }
            $newAttributeKey = $this->determineAttributeKey($attributesMapping, $name, $attributeValues, $newAttributes, $sProductType);
            $storeAttributes[$newAttributeKey] = $data;
            if (isset($attributeValues)) {
                $storeAttributes = $this->transformAttributesForMigration($storeAttributes, $attributesMapping, $snakeCaseName, $newAttributeKey, $data, $attributeValues, $newAttributes);
            }
        }

        if (!empty($storeAttributes)) {
            $this->updateProductData([
                'ShopVariation' => json_encode($storeAttributes),
                'IsComplete' => 'migration'
            ], $productId);
        }
    }

    /**
     * Matches and processes attribute values based on a provided mapping. Updates the store attributes
     * configuration with the corresponding marketplace values. If a value is not found in the mapping,
     * it retrieves additional attributes using a helper method.
     *
     * @param array $storeAttributes The array of store attributes to be updated with matched or newly fetched values.
     * @param array $attributesMapping The mapping between store attributes and their corresponding marketplace values.
     * @param string $snakeCaseName The snake_case name of the attribute being processed.
     * @param string $newAttributeKey The key representing the current attribute in the store attributes array.
     * @param array $attributeValues The array of attribute values being processed for the current attribute.
     * @param string $matchKey Key identifying the current value being processed within the attribute.
     * @param array $newAttributes Array containing definitions of new attributes to be used when fetching unmatched values.
     * @return array The updated store attributes array with matched or fetched attribute values.
     */
    private function matchAttributeValues($storeAttributes, $attributesMapping, $snakeCaseName, $newAttributeKey, $attributeValues, $matchKey, $newAttributes) {
        if (array_key_exists($attributeValues['Value'], $attributesMapping[$snakeCaseName])) {
            $jsonValue = $attributesMapping[$snakeCaseName][$attributeValues['Value']]['JSON Value'];
            $storeAttributes[$newAttributeKey]['Values'][$matchKey]['Marketplace']['Value'] = $jsonValue;
            $storeAttributes[$newAttributeKey]['Values'][$matchKey]['Marketplace']['Key'] = $jsonValue;
        } else {
            $storeAttributes = $this->getAttributes($attributeValues['Value'], $newAttributes[$newAttributeKey]['values'], $newAttributeKey, $storeAttributes, $matchKey);
        }
        return $storeAttributes;
    }

    /**
     * Processes and updates a set of store attributes by matching attribute values with a predefined set
     * of new attributes and their corresponding keys.
     *
     * @param array $storeAttributes The current array of store attributes to be updated.
     * @param string $newAttributeKey The key of the new attribute to be matched and processed.
     * @param array $attributeValues An array of attribute values to be processed for matching.
     * @param array $newAttributes An array of new attributes with associated details, including possible values.
     *
     * @return array Updated store attributes after processing and matching the attribute values.
     */
    private function handleSelectAttributes($storeAttributes, $newAttributeKey, $attributeValues, $newAttributes) {
        foreach ($attributeValues as $matchKey => $attributeValue) {
            $storeAttributes = $this->getAttributes($attributeValue['Value'], $newAttributes[$newAttributeKey]['values'], $newAttributeKey, $storeAttributes, $matchKey);
        }
        return $storeAttributes;
    }

    /**
     * Processes and maps single attribute values based on predefined mappings and configurations.
     * Updates the provided attribute store with transformed or new attribute values.
     *
     * @param array $storeAttributes An associative array of attributes where modifications will be stored.
     * @param array $attributesMapping An associative mapping of attribute keys and possible values for processing.
     * @param string $snakeCaseName The attribute name in snake_case format to locate specific mappings.
     * @param string $newAttributeKey The key under which the transformed attribute data will be stored.
     * @param array $data Associative array containing detailed attribute data to be processed.
     * @param array $newAttributes An array of new attributes generated during the process, used for reverse lookups.
     * @param mixed $attributeValues The original value of the attribute to be processed and transformed.
     *
     * @return array Updated associative array of attributes with processed and transformed values.
     */
    private function handleSingleAttributeValues($storeAttributes, $attributesMapping, $snakeCaseName, $newAttributeKey, $data, $newAttributes, $attributeValues) {
        if (isset($attributesMapping[$snakeCaseName][$attributeValues])) {
            $attributeValues = $attributesMapping[$snakeCaseName][$attributeValues]['JSON Value'];
            $data[$snakeCaseName]['Values'] = $attributeValues;
            $storeAttributes[$newAttributeKey] = $data;
        }
        else if (isset($attributesMapping[$snakeCaseName]['Converted JSON Pointer'])) {
            $desensitizedAttributeValue = $this->desanitizeString($attributeValues);
            $newAttributeValue = array_search(
                $desensitizedAttributeValue,
                (isset($newAttributes[$newAttributeKey]['values']) ? $newAttributes[$newAttributeKey]['values'] : [])
            );
            if ($newAttributeValue !== false) {
                $storeAttributes[$newAttributeKey]['Values'] = $newAttributeValue;
            }
        }
        return $storeAttributes;
    }

    /**
     * Extracts attribute values from the provided attribute data array.
     *
     * @param array $aAttributeData An associative array containing attribute data, including a 'Values' key.
     *                              If 'Values' is an array and 'DataType' is not 'text', marketplace-specific values are extracted.
     * @return array|string|null An array of extracted attribute values indexed by keys, or null if no values are available.
     */
    private function extractAttributeValuesFromData($aAttributeData) {
        $result = null;
        if (!empty($aAttributeData['Values'])) {
            if (is_array($aAttributeData['Values'])) {
                // we do not change the free text type attribute values
                if ($aAttributeData['DataType'] !== 'text') {
                    foreach ($aAttributeData['Values'] as $key => $value) {
                        $result[$key] = $value['Marketplace'];
                    }
                }
            } else {
                $result = $aAttributeData['Values'];
            }
        }

        return $result;
    }

    /**
     * Determines and returns the appropriate attribute key based on the provided mapping, old attribute name,
     * attribute values, new attributes, and product type. The method performs multiple checks to select the
     * most suitable key based on the provided data.
     *
     * @param array $attributesMapping An associative array that maps attribute names and values to their corresponding data.
     * @param string $oldAttributeName The original name of the attribute being processed.
     * @*/
    private function determineAttributeKey($attributesMapping, $oldAttributeName, $attributeValues, $newAttributes, $sProductType) {
        $snakeCaseName = $this->convertStringToSnakeCase($oldAttributeName);
        if (is_array($attributeValues)) {
            $attributeValue = isset($attributeValues['Value']) ? $attributeValues['Value'] : null;
        } else {
            $attributeValue = $attributeValues;
        }

        if (isset($attributesMapping[$snakeCaseName][$attributeValue])) {
            return $attributesMapping[$snakeCaseName][$attributeValue]['Converted JSON Pointer'];
        }

        if (isset($attributesMapping[$snakeCaseName]['Converted JSON Pointer'])) {
            return $attributesMapping[$snakeCaseName]['Converted JSON Pointer'];
        }

        if (array_key_exists($oldAttributeName.'__value', $newAttributes)) {
            return $oldAttributeName.'__value';
        }

        return reset($attributesMapping[$snakeCaseName])['Converted JSON Pointer'];
    }

    /**
     * Transforms and processes store attributes for data migration by handling attribute values
     * and mapping them to new attribute structures. The method determines the type of attribute values
     * (array or single value) and delegates processing through specific helper methods.
     *
     * @param array $storeAttributes An array containing the current store attributes to be processed or modified.
     * @param array $attributesMapping An associative array where keys represent attribute names in snake case
     *                                  and values provide attribute mappings or metadata.
     * @param string $snakeCaseName The snake_case name of the attribute being processed.
     * @param string $newAttributeKey The key representing the new attribute in the migrated data structure.
     * @param array $data Additional data or context required for processing attributes.
     * @param mixed $attributeValues The value(s) of the attribute to be processed, which may be an array or a single value.
     * @param array $newAttributes A collection of new attributes being constructed during the migration.
     *
     * @return array Returns the modified $storeAttributes array after processing and transforming the attributes
     *               based on their type and mapping.
     */
    private function transformAttributesForMigration($storeAttributes, $attributesMapping, $snakeCaseName, $newAttributeKey, $data, $attributeValues, $newAttributes) {
        if (is_array($attributeValues)) {
            if (!isset($attributesMapping[$snakeCaseName]['Flat File Attribute Name'])) {
                foreach ($attributeValues as $matchKey => $attributeValue) {
                    $storeAttributes = $this->matchAttributeValues(
                        $storeAttributes,
                        $attributesMapping,
                        $snakeCaseName,
                        $newAttributeKey,
                        $attributeValue,
                        $matchKey,
                        $newAttributes
                    );
                }
            } else {
                $storeAttributes = $this->handleSelectAttributes(
                    $storeAttributes,
                    $newAttributeKey,
                    $attributeValues,
                    $newAttributes
                );
            }
        } else {
            $storeAttributes = $this->handleSingleAttributeValues(
                $storeAttributes,
                $attributesMapping,
                $snakeCaseName,
                $newAttributeKey,
                $data,
                $newAttributes,
                $attributeValues
            );
        }
        return $storeAttributes;
    }

    /**
     * Migriert BrowseNodes vom alten Format zum neuen Format durch Matching
     *
     * @param string $oldBrowseNode Der BrowseNode im alten Format
     * @param array $newBrowseNodes Array von verfügbaren BrowseNodes im neuen Format
     * @return string|null Das komplette neue Format bei Match, sonst null
     */
    public function migrateBrowseNode($oldBrowseNode, array $newBrowseNodes) {
        try {
            $oldBrowseNode = json_decode($oldBrowseNode, true) ?: array();
            $oldBrowseNode = current($oldBrowseNode) ?: array();
            $oldParts = explode('__', current($oldBrowseNode));

            if (count($oldParts) < 2) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

        $firstPart = $oldParts[0];
        $secondPart = $oldParts[1];

        foreach ($newBrowseNodes as $newBrowseNode => $categoryName) {
            $newParts = explode('__', $newBrowseNode);

            if (count($newParts) < 1) {
                continue;
            }

            // Fall 1: Erste und zweite Teil in umgekehrter Reihenfolge am Ende
            if (count($newParts) >= 2) {
                $secondLastPart = $newParts[count($newParts) - 2];
                $lastPart = $newParts[count($newParts) - 1];

                if ($firstPart === $lastPart && $secondPart === $secondLastPart) {
                    MLMessage::gi()->addDebug('found?', $newBrowseNode);
                    return $newBrowseNode;
                }
            }

            // Fall 2: Erster Teil vom alten = letzter Teil vom neuen
            $lastNewPart = end($newParts);
            if ($firstPart === $lastNewPart) {
                return $newBrowseNode;
            }
        }

        return null;
    }

    /**
     * Converts a camel case string to snake case.
     *
     * @param string $name The camel case string to be converted.
     * @return string The converted string in snake case format.
     */
    private function convertStringToSnakeCase($name) {
        // convert the attribute name from camel case to snake case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }

    /**
     * Converts a sanitized string back to its original form by decoding it using JSON encoding.
     * This is useful for restoring strings that might have been encoded or escaped for safety purposes.
     *
     * @param string $sanitizedString The string that has been sanitized and needs to be desanitized.
     * @return string|null The original desanitized string, or null if the decoding process fails.
     */
    private function desanitizeString($sanitizedString) {
        return json_decode('"' . $sanitizedString . '"');
    }

    /**
     * Fetches a list of product types by making a cached request to the API.
     * The method processes the API response, modifies the product type keys by removing underscores,
     * and returns an associative array mapping transformed keys to original product type keys.
     *
     * @return array Associative array of product types where keys are transformed (underscores removed and converted to lowercase)
     *               and values are the original product type keys from the API response. Returns an empty array on failure.
     */
    private function callGetListingProductTypes() {
        try {
            $requestParams = array(
                'ACTION' => 'GetAllProductTypes',
            );
            $response = MagnaConnector::gi()->submitRequestCached($requestParams, 60 * 60 * 8);
            if (isset($response['DATA'])) {
                $result = array();
                foreach ($response['DATA'] as $productTypeKey => $productType) {
                    // remove underscore so we can match the product types to old API
                    $result[strtolower(str_replace('_', '', $productTypeKey))] = $productTypeKey;
                }

                return $result;
            }

            return array();
        } catch (MagnaException $oEx) {
            MLMessage::gi()->addDebug($oEx);
            return array();
        }
    }

    /**
     * Retrieves category details for a given product type by making a cached request to the API.
     * The method processes the API response and returns the list of attributes associated with the specified product type.
     *
     * @param string $sProductType The product type for which category details should be fetched. The value is converted to uppercase before the request.
     *
     * @return array Array of attributes associated with the specified product type as returned by the API. Returns an empty array if the product type is not set or if an error occurs.
     */
    protected function callGetListingCategoryDetails($sProductType) {
        if (!isset($sProductType)) {
            return array();
        }

        try {
            $requestParams = array(
                'ACTION'   => 'GetCategoryDetails',
                'DATA' => array(
                    'PRODUCTTYPE' => strtoupper($sProductType)
                )
            );
            $response = MagnaConnector::gi()->submitRequestCached($requestParams, 60 * 60 * 8);

            return isset($response['DATA']['attributes']) ? $response['DATA']['attributes'] : array();
        } catch (MagnaException $oEx) {
            MLMessage::gi()->addDebug($oEx);
            return array();
        }
    }

    /**
     * Sends a request to retrieve the attributes migration map and returns the resulting data.
     *
     * @return array The attributes migration map data, or an empty array if no data is available.
     * @throws Exception If an error occurs during the request process.
     */
    protected function callGetAttributesMigrationMap() {
        try {
            $requestParams = array(
                'ACTION' => 'GetAttributesMigrationMap',
            );
            $response = MagnaConnector::gi()->submitRequestCached($requestParams, 60 * 60 * 8);

            return isset($response['DATA']) ? $response['DATA'] : array();
        } catch (MagnaException $oEx) {
            MLMessage::gi()->addDebug($oEx);
            throw new Exception($oEx->getMessage(), $oEx->getCode());
        }
    }

    /**
     * Retrieves browse nodes for a given product type by making a cached request to the API.
     * The method processes the API response and returns the data if available.
     * Throws an exception in case of an error during the API request.
     *
     * @param string $productType The product type for which to fetch browse nodes.
     *
     * @return array Array of browse nodes data returned by the API. Returns an empty array if no data is available.
     *
     * @throws Exception Throws an exception if an error occurs during the API request.
     */
    protected function callGetBrowseNodes($productType) {
        try {
            $requestParams = array(
                'ACTION' => 'GetBrowseNodes',
                'CATEGORY' => $productType,
                'NewResponse' => 'ALL',
                'Version' => 2,
            );
            $response = MagnaConnector::gi()->submitRequestCached($requestParams, 60 * 60 * 8);

            return isset($response['DATA']) ? $response['DATA'] : array();
        } catch (MagnaException $oEx) {
            MLMessage::gi()->addDebug($oEx);
            throw new Exception($oEx->getMessage(), $oEx->getCode());
        }
    }

    /**
     * @param $value
     * @param $values
     * @param $newAttributeKey
     * @param $storeAttributes
     * @param $matchKey
     * @return array
     */
    public function getAttributes($value, $values, $newAttributeKey, $storeAttributes, $matchKey) {
        $desensitizedAttributeValue = $this->desanitizeString($value);
        $newAttributeValue = array_search(
            $desensitizedAttributeValue,
            (isset($values) ? $values : [])
        );
        if ($newAttributeValue !== false) {
            $storeAttributes[$newAttributeKey]['Values'][$matchKey]['Marketplace']['Value'] = $newAttributeValue;
            $storeAttributes[$newAttributeKey]['Values'][$matchKey]['Marketplace']['Key'] = $newAttributeValue;
        }
        return $storeAttributes;
    }

    /**
     * Updates specified fields in the database for a specific product ID.
     * The method updates the given fields of the 'magnalister_amazon_prepare' table
     * for the specified product ID and marketplace ID.
     *
     * @param array $updateData Associative array of field names and values to update in the database
     * @param mixed $productsID The ID of the product to be updated.
     *                          This can be a single value or an array of IDs.
     * @return void
     */
    public function updateProductData(array $updateData, $productsID) {
        MLDatabase::getDbInstance()->update('magnalister_amazon_prepare',
            $updateData,
            [
                'ProductsID' => $productsID,
                'mpID' => MLModule::gi()->getMarketPlaceId()
            ]
        );
    }

}
