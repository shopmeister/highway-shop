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

/**
 * Makes sure every hitmeister marketplace has the currency setting saved.
 */
class ML_Hitmeister_Update_CheckCurrencyConfiguration extends ML_Core_Update_Abstract
{
    /**
     */
    public function execute()
    {
        $db = MLDatabase::getDbInstance();
        if (!$db->tableExists('magnalister_config')) {
            return;
        }

        foreach (MLShop::gi()->getMarketplaces() as $id => $marketplace) {
            if ('hitmeister' !== $marketplace) {
                continue;
            }

            $selectQuery = MLDatabase::factorySelectClass();
            $count = (int)$selectQuery
                ->from('magnalister_config')
                ->where("mpID = '".$db->escape($id)."' AND mkey = 'currency'")
                ->getCount();
            if (0 < $count) {
                continue;
            }

            try {
                $response = MagnaConnector::gi()->submitRequestCached(
                    [
                        'SUBSYSTEM' => 'hitmeister',
                        'MARKETPLACEID' => $id,
                        'ACTION' => 'GetCurrencies',
                    ]
                );
                if (   !is_array($response) || empty($response['STATUS']) || 'SUCCESS' !== $response['STATUS']
                    || empty($response['DATA']) || !is_array($response['DATA'])
                ) {
                    continue;
                }
            } catch (Exception $e) {
                continue;
            }

            $selectQuery = MLDatabase::factorySelectClass();
            $siteConfig = $selectQuery->from('magnalister_config')
                ->where('mpID = '.$db->escape($id)." AND mkey = 'site'")
                ->getResult();

            if (empty($siteConfig)) {
                continue;
            }

            foreach ($siteConfig as $config) {
                if (empty($response['DATA'][$config['value']])) {
                    break;
                }

                $db->insert('magnalister_config', [
                    'mpID' => $config['mpID'],
                    'mkey' => 'currency',
                    'value' => $response['DATA'][$config['value']]
                ]);

                break;
            }
        }
    }
}
