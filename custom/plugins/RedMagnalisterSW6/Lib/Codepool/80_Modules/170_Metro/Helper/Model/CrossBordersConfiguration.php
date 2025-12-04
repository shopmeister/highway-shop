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

/**
 * Data and logic for cross border configurations.
 *
 * When a new instance is created, it loads all needed configuration variables for metro tabs from the database.
 */
class ML_Metro_Helper_Model_CrossBordersConfiguration {
    /**
     * The key is the marketplace id.
     *
     * @var array<int,array{
     *      mpID:int,
     *      clientkey:string,
     *      maxquantity:int,
     *      "quantity.type":string,
     *      "quantity.value":int,
     *      "shippingorigin":string,
     *      "stocksync.tomarketplace":string
     * }>
     */
    private $configData = array();

    /**
     * A static instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Loads the configuration.
     */
    public function __construct() {
        $this->load();
    }

    /**
     * Return the number of marketplaces in the configuration.
     *
     * @return int
     */
    public function countMarketplaces() {
        return count($this->configData);
    }

    /**
     * Return the value for the key to the marketplace.
     *
     * @param int $marketplaceId
     * @param string $key
     * @return int|string
     */
    public function get($marketplaceId, $key) {
        if (!array_key_exists($marketplaceId, $this->configData) ||
            !array_key_exists($key, $this->configData[$marketplaceId])
        ) {
            return null;
        }

        return $this->configData[$marketplaceId][$key];
    }

    /**
     * It returns an array with all marketplaces which are responsible for stock options.
     *
     * @param int $mpID
     * @return array<string,array{
     *     origin:string
     * }> the array key is in the format "<clientkey>|<shippingorigin>"
     */
    public function getCrossBorderSettings($mpID) {
        $crossBordersSettings = array();
        $currentMarketplace = $this->getMarketplace($mpID);
        foreach ($this->iterateMarketplaces() as $marketplace) {
            $key = $marketplace['clientkey'].'|'.$marketplace['shippingorigin'];
            if ('auto' == $marketplace['stocksync.tomarketplace']
                && $marketplace['clientkey'] == $currentMarketplace['clientkey']
                && $marketplace['mpID'] != $mpID
            ) {
                if (!array_key_exists($key, $crossBordersSettings)) {
                    $crossBordersSettings[$key] = array(
                        'mpID' => $marketplace['mpID'],
                        'origin' => $marketplace['shippingorigin'],
                    );
                }
            }
        }

        return $crossBordersSettings;
    }

    /**
     * Returns the first marketplace id which has the same client key and shipping origin as the provided marketplace id
     *  and has stock synchronization enabled.
     *
     * @param int $marketplaceId The marketplace id to check the client key with.
     * @return int|null
     */
    public function getCrossBordersStockOptionsMarketplaceId($marketplaceId) {
        $marketplaceCnt = $this->countMarketplaces();

        if ($marketplaceCnt) {
            $currentClientKey = $this->get($marketplaceId, 'clientkey');
            $currentShippingOrigin = $this->get($marketplaceId, 'shippingorigin');
            foreach ($this->iterateMarketplaces() as $marketplace) {
                if ('auto' == $marketplace['stocksync.tomarketplace']
                    && $currentClientKey == $marketplace['clientkey']
                    && $currentShippingOrigin == $marketplace['shippingorigin']
                ) {
                    return $marketplace['mpID'];
                }
            }
        }

        return null;
    }

    /**
     * Get all config data for a marketplace id.
     *
     * @param int $marketplaceId
     * @return array{
     *      mpID:int,
     *      clientkey:string,
     *      maxquantity:int,
     *      "quantity.type":string,
     *      "quantity.value":int,
     *      shippingorigin:string,
     *      shippingdestination:string,
     *      "stocksync.tomarketplace":string
     *  }|null
     */
    public function getMarketplace($marketplaceId) {
        if (!array_key_exists($marketplaceId, $this->configData)) {
            return null;
        }

        return $this->configData[$marketplaceId];
    }

    /**
     * Get all metro marketplace ids from the global configuration.
     *
     * @return int[]
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    public function getMetroMarketplaceIds() {
        $metroIds = array();
        try {
            $shopInfo = MLShop::gi()->getShopInfo();
            if (!empty($shopInfo['DATA']['Marketplaces'])) {
                foreach ($shopInfo['DATA']['Marketplaces'] as $marketplace) {
                    if ('metro' == $marketplace['Marketplace']) {
                        $metroIds[] = (int)$marketplace['ID'];
                    }
                }
            }
        } catch (Exception $ex) {
            MLMessage::gi()->addDebug($ex);
        }
        return $metroIds;
    }

    /**
     * Return a static instance.
     *
     * @return self
     */
    public static function gi() {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Iterates through the marketplaces.
     *
     * @return Generator
     */
    public function iterateMarketplaces() {
        foreach ($this->configData as $entry) {
            yield $entry;
        }
    }

    /**
     * Return a marketplace which is responsible for the stock options, other than the omitted marketplace id.
     *
     * Returns null, if none found, so the current marketplace can be responsible for the stock options.
     *
     * @param int $marketplaceId
     * @param string $clientKey
     * @param string $shippingOrigin
     * @return array{
     *     mpID:int,
     *     clientkey:string,
     *     maxquantity:int,
     *     "quantity.type":string,
     *     "quantity.value":int,
     *     shippingorigin:string,
     *     shippingdestination:string,
     *     "stocksync.tomarketplace":string
     * }|null
     */
    public function getOtherStockMarketplaceForSettings($marketplaceId, $clientKey, $shippingOrigin) {
        foreach ($this->iterateMarketplaces() as $marketplace) {
            if ($marketplace['mpID'] != $marketplaceId
                && $marketplace['clientkey'] == $clientKey
                && $marketplace['shippingorigin'] == $shippingOrigin
                && 'auto' == $marketplace['stocksync.tomarketplace']
            ) {
                return $marketplace;
            }
        }

        return null;
    }

    /**
     * Iterates through all marketplaces which have the same metro account and the same origin and doesn't have stock
     * synchronization enabled.
     *
     * @param int $marketplaceId
     * @return Generator
     */
    public function iterateSameCrossBorderMarketplaces($marketplaceId) {
        $crossBorderSettings = $this->getMarketplace($marketplaceId);
        foreach ($this->iterateMarketplaces() as $marketplace) {
            if ($crossBorderSettings['mpID'] != $marketplace['mpID']
                && $crossBorderSettings['clientkey'] == $marketplace['clientkey']
                && $crossBorderSettings['shippingorigin'] == $marketplace['shippingorigin']
                && 'no' == $marketplace['stocksync.tomarketplace']
            ) {
                yield $marketplace;
            }
        }
    }

    /**
     * Loads all needed configuration variables for all metro marketplaces from the database.
     *
     * @return void
     */
    public function load() {
        $metroIds = $this->getMetroMarketplaceIds();
        if (empty($metroIds)) {
            return;
        }

        $dbData = MLDatabase::getDbInstance()->fetchArray(sprintf("
            SELECT *
              FROM magnalister_config
             WHERE mpID IN (%s) AND mkey IN ('clientkey', 'maxquantity', 'quantity.type',
                    'quantity.value', 'shippingorigin', 'shippingdestination', 'stocksync.tomarketplace')
            ", implode(', ', $metroIds)));

        foreach ($dbData as $entry) {
            $this->set($entry['mpID'], $entry['mkey'], $entry['value']);
        }
    }

    /**
     * Set a value for a key to a marketplace id.
     *
     * @param int $marketplaceId
     * @param string $key
     * @param string $value
     * @return self
     */
    public function set($marketplaceId, $key, $value) {
        if (!array_key_exists($marketplaceId, $this->configData)) {
            $this->configData[$marketplaceId] = array(
                'mpID' => (int)$marketplaceId
            );
        }

        $this->configData[$marketplaceId][$key] = $value;

        return $this;
    }


    public function fixConfigurationForMarketplaceWithSameOrigin( $crossBordersConf) {
        if (1 < $this->countMarketplaces()) {
            $groups = array();
            foreach ($this->iterateMarketplaces() as $marketplace) {
                // Skip marketplaces without required keys
                if (!isset($marketplace['clientkey']) || !isset($marketplace['shippingorigin'])) {
                    continue;
                }
                $groupKey = $marketplace['clientkey'] . ';' . $marketplace['shippingorigin'];
                if (!array_key_exists($groupKey, $groups)) {
                    $groups[$groupKey] = array();
                }
                $groups[$groupKey][$marketplace['mpID']] = $marketplace;
            }

            $db = MLDatabase::getDbInstance();
            foreach ($groups as $group) {
                $first = null;
                foreach ($group as $marketplaceId => $setting) {
                    if (isset($setting['stocksync.tomarketplace']) && 'auto' === $setting['stocksync.tomarketplace']) {
                        // first one goes through, all others will be updated to no and quantity settings set to the
                        // first one
                        if (null === $first) {
                            $first = $setting;
                        } else {
                            $db->update('magnalister_config', array('value' => 'no'),
                                array('mpID' => $marketplaceId, 'mkey' => 'stocksync.tomarketplace'));
                            if (isset($first['maxquantity'])) {
                                $db->update('magnalister_config', array('value' => $first['maxquantity']),
                                    array('mpID' => $marketplaceId, 'mkey' => 'maxquantity'));
                            }
                            if (isset($first['quantity.type'])) {
                                $db->update('magnalister_config', array('value' => $first['quantity.type']),
                                    array('mpID' => $marketplaceId, 'mkey' => 'quantity.type'));
                            }
                            if (isset($first['quantity.value'])) {
                                $db->update('magnalister_config', array('value' => $first['quantity.value']),
                                    array('mpID' => $marketplaceId, 'mkey' => 'quantity.value'));
                            }
                        }
                    }
                }
            }
        }
    }
}
