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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

MLFilesystem::gi()->loadClass('Shopware6_Helper_Model_ProductList_List');

class ML_Shopware66_Helper_Model_ProductList_List extends ML_Shopware6_Helper_Model_ProductList_List
{
    public function shopSystemAttribute($sCode, $blUse = true, $sTitle = null, $sTypeVariant = null) {
        if ($this->oLoadedProduct === null) {
            if (!in_array($sCode, $this->aFields)) {
                /**
                 * \Shopware\Core\Framework\DataAbstractionLayer\CompiledFieldCollection::getMappedByStorageName
                 * @return list<string>
                 * @deprecated tag:v6.6.0 - Will be removed without replacement as it is unused
                 */
                if (!in_array($sCode, $this->aFields)) {
                    if ($blUse && array_key_exists($sCode, $this->aAttributes)) {
                        $this->aFields[] = '_' . $sCode;
                        $this->aHeader['_' . $sCode] = array('title' => $sTitle === null ? (isset($this->aAttributes[$sCode]) ? $this->aAttributes[$sCode] : ucfirst($sCode)) : $sTitle, 'order' => $sCode, 'type' => 'simpleText', 'type_variant' => $sTypeVariant === null ? 'simpleText' : $sTypeVariant);
                    }
                }
            }
            return $this;
        } else {
            $sCode = substr($sCode, 1);
            $mValue = $this->oLoadedProduct->getProductField($sCode);
            return in_array($mValue, array('', null)) ? '-' : $mValue;
        }
    }
}
