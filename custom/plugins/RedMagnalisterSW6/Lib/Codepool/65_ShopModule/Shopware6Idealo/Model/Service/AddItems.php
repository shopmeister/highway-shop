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


MLFilesystem::gi()->loadClass('Idealo_Model_Service_AddItems');


class ML_Shopware6Idealo_Model_Service_AddItems extends ML_Idealo_Model_Service_AddItems {

    /**
     * Retrieves the campaign link with an appended campaign parameter if defined.
     *
     * @return string The campaign link with the appropriate campaign parameter appended, or an empty string if no link is defined.
     */
    public function getCampaignLink(): string {
        $sCampaingLink = MLModule::gi()->getConfig('campaignlink');

        if (empty($sCampaingLink)) {
            return '';
        }

        $sCampaingParameterName = MLModule::gi()->getConfig('campaignparametername');
        if (empty($sCampaingParameterName)) {
            $sCampaingParameterName = 'campaignCode';
        }

        return '/?'.$sCampaingParameterName.'='.$sCampaingLink;
    }
}