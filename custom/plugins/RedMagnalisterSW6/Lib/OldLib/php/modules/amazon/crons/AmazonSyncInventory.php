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

if (!defined('_ML_INSTALLED'))
    throw new Exception('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class AmazonSyncInventory extends MagnaCompatibleSyncInventory {

    protected function getPriceObject(){
        //$oProduct=$this->oProduct;// amazon don't need it
        return MLModule::gi()->getPriceObject();
    }

    /**
     * Overwrite the generic updatePrice function so it returns always false
     *      But the parent function is used in updateCustomFields()
     *
     * @return bool|float
     */
    protected function updatePrice() {
        return false;
    }

    /**
     * Updates the custom fields for the provided data array by modifying or adding BusinessPrice, Price, and ShippingTime as required.
     *
     * @param array $data The reference to the data array to be updated with custom fields such as BusinessPrice, Price, and ShippingTime.
     * @return void
     */
    protected function updateCustomFields(&$data) {
        $businessPrice = $this->updateBusinessPrice();

        if ($businessPrice) {
            $data['BusinessPrice'] = $businessPrice;

            // the price needs to be submitted if it's not only B2B otherwise it will be after only B2B on Amazon
            if (!array_key_exists('Price', $data) && ((float)$this->cItem['Price'] !== 0.0 && $this->cItem['Price'] !== null)) {
                $data['Price'] = parent::updatePrice();

                // if updatePrice() === false - so price is not changed, use price from inventory response
                // needed to submit, so magnalister API knows to update B2C and B2B price
                if ($data['Price'] === false) {
                    $data['Price'] = (float)$this->cItem['Price'];
                }
            }
        } elseif ($this->cItem['Price'] !== null && (float)$this->cItem['Price'] !== 0.0) {
            // if b2b price is not changed, we need to check also for default price
            $price = parent::updatePrice();

            // if updatePrice() !== false - so price is changed
            if ($price !== false) {
                $data['Price'] = $price;

                // so b2b price was not changed so use data from inventory response
                if (array_key_exists('BusinessPrice', $this->cItem)) {
                    $data['BusinessPrice'] = (float)$this->cItem['BusinessPrice'];
                }
            }
        }

        // If we don't get Price from API, the item is B2B only!
        if ($this->cItem['Price'] === null) {
            unset($data['Price']);
        }

		if (empty($data)) {
			return;
		}

        // Set shipping time
        $data['ShippingTime'] = amazonGetLeadtimeToShip($this->mpID, $this->cItem['pID']);
	}

    protected function getStockConfig() {
        return MLModule::gi()->getStockConfig();
    }

	/**
	 * We need to return false if no business price is provided by API but we need to always return business price when provided
	 *
	 * @return bool|float
	 */
	protected function updateBusinessPrice() {
		if (!$this->oProduct->exists() || !$this->syncPrice || !isset($this->cItem['BusinessPrice'])) {
			return false;
		} else {
			$data = false;
			try{
				$price = $this->oProduct->getSuggestedMarketplacePrice($this->getBusinessPriceObject());
				if (($price > 0) && ((float) $this->cItem['BusinessPrice'] != $price)) {
					$this->log("\n\t" .
						'Business Price changed (old: ' . $this->cItem['BusinessPrice'] . '; new: ' . $price . ')'
					);
					$data = $price;
				} else {
					$this->log("\n\t" .
						'Business Price not changed (' . $price . ')'
					);
				}
			}  catch (Exception $oExc){
				$this->log("\n\t" .$oExc->getMessage());
			}

			return $data;
		}
	}

	/**
	 * Configures price-object
	 * @return ML_Shop_Model_Price_Interface
	 */
	private function getBusinessPriceObject() {
		$sKind = MLModule::gi()->getConfig('b2b.price.addkind');
		if (isset($sKind)) {
			$fFactor = (float)MLModule::gi()->getConfig('b2b.price.factor');
			$iSignal = MLModule::gi()->getConfig('b2b.price.signal');
			$iSignal = $iSignal === '' ? null : (int)$iSignal;
			$blSpecial = (boolean)MLModule::gi()->getConfig('b2b.price.usespecialoffer');
			$sGroup = MLModule::gi()->getConfig('b2b.price.group');
			$oPrice = MLPrice::factory()->setPriceConfig($sKind, $fFactor, $iSignal, $sGroup, $blSpecial);
		} else {
			$oPrice = $this->getPriceObject();
		}

		return $oPrice;
	}

}
