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

MLFilesystem::gi()->loadClass('Shopware6_Model_Product');

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ML_Shopware6Idealo_Model_Product extends ML_Shopware6_Model_Product {

    public function getFrontendLink() {
        $this->load();
        $sShopwareDomain = MLModule::gi()->getConfig('preparedomain');
        if (empty($sShopwareDomain)) {
            $sShopwareDomain = MagnalisterController::getShopwareRequest()->server->get('APP_URL');
        }
        if ($this->get('parentid') == 0) {
            return $sShopwareDomain.'/detail/'.$this->getMasterProductEntity()->getId();
        } else {
            return $sShopwareDomain.'/detail/'.$this->get('productsid');
        }
    }

    /**
     * function to get data and put to cache
     *
     * @param Criteria $criteria
     * @param $media
     * @return string
     */
    public function getMediaUrlInMediaRepositoryById($criteria, $media) {
        $oMedia = MagnalisterController::getShopwareMyContainer()
            ->get('media.repository')
            ->search(
                $criteria->addFilter(
                    new EqualsFilter('id', $media->getMediaId())
                ), $this->getShopwareContext()
            )->first();
        $imgUrl = $oMedia->getUrl();

        try {
            $aMediaCache = MLSetting::gi()->get('oShopwareMediaCache', array());
        } catch (Exception $ex) {
            $aMediaCache = array();
        }
        $aMediaCache[$imgUrl] = $oMedia;
        MLSetting::gi()->set('oShopwareMediaCache', $aMediaCache, true);

        return $this->getImagePath($imgUrl);
    }

    private function getImagePath($sImageUrl){
        $sShopwareDomain = MLModule::gi()->getConfig('preparedomain');
        if (mb_substr($sImageUrl, 0, strlen($sShopwareDomain)) == $sShopwareDomain) {
            return $sImageUrl;
        }

        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(new EqualsFilter('active', true));
        $salesChannels = MLShopware6Alias::getRepository('sales_channel.repository')->search($criteria, Context::createDefaultContext())->getEntities();
        $sImagePath = '';
        foreach ($salesChannels as $salesChannel) {
            $domains = $salesChannel->getDomains();
            if ($domains->count() > 0) {
                foreach($domains as $domain) {
                    $domainUrl = $domain->getUrl();
                    if (mb_strpos($sImageUrl, $domainUrl) === 0) {
                        if (mb_substr($sImageUrl, 0, mb_strlen($domainUrl)) == $domainUrl) {
                            $sImagePath = mb_substr($sImageUrl, mb_strlen($domainUrl));
                        }
                    }
                }
            }
        }

        // If we did not find a matching domain, it means a cdn domain is used, we cannot edit the domain
        if ('' === $sImagePath) {
            return $sImageUrl;
        }

        return $sShopwareDomain.$sImagePath;
    }
}
