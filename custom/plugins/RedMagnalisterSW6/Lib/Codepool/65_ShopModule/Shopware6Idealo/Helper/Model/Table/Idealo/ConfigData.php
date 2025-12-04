<?php
/**
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
 * $Id$
 *
 * (c) 2010 - 2018 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLFilesystem::gi()->loadClass('Idealo_Helper_Model_Table_Idealo_ConfigData');

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ML_Shopware6Idealo_Helper_Model_Table_Idealo_ConfigData extends ML_Idealo_Helper_Model_Table_Idealo_ConfigData {

    public function prepareDomainField(&$aField){
        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(new EqualsFilter('active', true));
        $salesChannels = MLShopware6Alias::getRepository('sales_channel.repository')->search($criteria, Context::createDefaultContext())->getEntities();
        $result = array('' => MLI18n::gi()->get('ConfigFormPleaseSelect'));
        foreach ($salesChannels as $salesChannel) {
            $domains = $salesChannel->getDomains();
            if ($domains->count() > 0) {
                foreach($domains as $domain) {
                    $domainUrl = $domain->getUrl();
                    $result[$salesChannel->getName()][$domainUrl] = $domainUrl;
                }
            }
        }

        $aField['values'] = $result;
    }
    
    
}
