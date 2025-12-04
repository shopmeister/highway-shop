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

MLFilesystem::gi()->loadClass('Core_Update_Abstract');

/**
 * Creates the default value for the configuration key `orderstatus.cancellation_reason` if it does not exist for
 * cdiscount marketplaces.
 */
class ML_Cdiscount_Update_OrderStatusCancellationReasonDefaultConfigurationUpdate extends ML_Core_Update_Abstract
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        try {
            /** @var ML_Base_Helper_Marketplace $helper */
            $helper = MLHelper::gi('marketplace');
            $marketplaces = $helper->magnaGetIDsByMarketplace('cdiscount');

            if (empty($marketplaces)) {
                return parent::execute();
            }

            foreach ($marketplaces as $marketplaceId) {
                /** @var ML_Database_Model_Table_Config $config */
                $config = MLDatabase::factory('config');
                /** @noinspection SpellCheckingInspection */
                $config->set('mpID', $marketplaceId)
                    ->set('mkey', 'orderstatus.cancellation_reason');
                $cancellationReason = $config->get('value');

                if (null === $cancellationReason) {
                    /** @var ML_Database_Model_Table_Config $config */
                    $config = MLDatabase::factory('config');
                    /** @noinspection SpellCheckingInspection */
                    $config->set('mpID', $marketplaceId)
                        ->set('mkey', 'orderstatus.cancellation_reason')
                        ->set('value', 'seller-refusal')
                        ->save();
                }
            }
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
        }

        return parent::execute();
    }
}
