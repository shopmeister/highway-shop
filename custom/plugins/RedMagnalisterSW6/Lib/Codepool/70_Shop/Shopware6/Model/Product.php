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

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\Tax\Aggregate\TaxRule\TaxRuleEntity;
use Shopware\Core\System\Tax\TaxRuleType\IndividualStatesRuleTypeFilter;
use Shopware\Core\System\Tax\TaxRuleType\ZipCodeRangeRuleTypeFilter;
use Shopware\Core\System\Tax\TaxRuleType\ZipCodeRuleTypeFilter;
use Shopware\Models\Shop\Currency;

class ML_Shopware6_Model_Product extends ML_Shop_Model_Product_Abstract {

    /** @var ProductEntity */
    protected $oProduct;
    protected $sPriceRules = '';
    protected $blIsMarketplacePrice = false;


    /**
     * @var Context
     */
    protected $oShopwareContext;
    /**
     * @var mixed|null
     */
    protected $oMasterProductEntityWithDefault;
    protected $oCorrespondingProductEntityWithDefault;

    /**
     * @param string $sName
     * @param mixed $mValue
     * @return mixed
     * @throws Exception
     */
    public function getProductCustomFieldValue($sName, $mValue,$customFieldType)
    {
        if($customFieldType == 'product'){
            if (isset($this->getCorrespondingProductEntity()->getCustomFields()[$sName])) {
                $mValue = $this->getCorrespondingProductEntity()->getCustomFields()[$sName];
            } else if (isset($this->getMasterProductEntity()->getCustomFields()[$sName])) {
                $mValue = $this->getMasterProductEntity()->getCustomFields()[$sName];
            } else if (isset($this->getCorrespondingProductEntityWithDefault()->getCustomFields()[$sName])) {
                $mValue = $this->getCorrespondingProductEntityWithDefault()->getCustomFields()[$sName];
            } else if (isset($this->getMasterProductEntityWithDefault()->getCustomFields()[$sName])) {
                $mValue = $this->getMasterProductEntityWithDefault()->getCustomFields()[$sName];
            }
        }elseif ($customFieldType == 'product_manufacturer'){
            if ($this->getCorrespondingProductEntity()->getManufacturer() !== null) {
                if (isset($this->getCorrespondingProductEntity()->getManufacturer()->getCustomFields()[$sName])) {
                    $mValue = $this->getCorrespondingProductEntity()->getManufacturer()->getCustomFields()[$sName];
                }
            } else if ($this->getMasterProductEntity()->getManufacturer() !== null) {
                if (isset($this->getMasterProductEntity()->getManufacturer()->getCustomFields()[$sName])) {
                    $mValue = $this->getMasterProductEntity()->getManufacturer()->getCustomFields()[$sName];
                }

            } else if ($this->getCorrespondingProductEntityWithDefault()->getManufacturer() !== null) {
                if (isset($this->getCorrespondingProductEntityWithDefault()->getManufacturer()->getCustomFields()[$sName])) {
                    $mValue = $this->getCorrespondingProductEntityWithDefault()->getManufacturer()->getCustomFields()[$sName];
                }
            } else if ($this->getMasterProductEntityWithDefault()->getManufacturer() !== null) {
                if (isset($this->getMasterProductEntityWithDefault()->getManufacturer()->getCustomFields()[$sName])) {
                    $mValue = $this->getMasterProductEntityWithDefault()->getManufacturer()->getCustomFields()[$sName];
                }

            }
        }

        return $mValue;
    }

    /**
     * @param mixed $mValue
     * @param $customField
     * @return false|mixed
     */
    public function getCustomFieldSelectTypeValue($mValue, $customField)
    {
        $sOptionTechnicalName = $mValue;
        $sLangCode = MLFormHelper::getShopInstance()->getLanguageCode();
        $customFiledOptions = $customField->getConfig()['options'];
        foreach ($customFiledOptions as $customFiledOption) {
            if ($customFiledOption['value'] === $sOptionTechnicalName) {
                if (isset($customFiledOption['label'][$sLangCode])) {
                    $mValue = $customFiledOption['label'][$sLangCode];
                } else {
                    $mValue = reset($customFiledOption['label']);
                }
            }
        }
        return $mValue;
    }

    /**
     * @return $this|ML_Shop_Model_Product_Abstract
     * @throws Exception
     */
    protected function loadShopProduct() {
        if ($this->oProduct === null) {//not loaded
            $this->oProduct = false; //not null
            if ($this->get('parentid') == 0) {
                $oProduct = $this;
            } else {
                $oProduct = $this->getParent();
            }
            $this->prepareProductForMarketPlace();
            $sKey = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.keytype')->get('value');
            if ($sKey == 'pID') {
                $oShopProduct = $this->getShopwareProduct($oProduct->get('productsid'));
            } else {
                $criteria = MLHelper::gi("model_product")->initiateCriteriaObjectWithMedia();
                $oShopwareProduct = MLShopware6Alias::getRepository('product')->search($criteria->addFilter(new EqualsFilter('productNumber', $oProduct->get('productssku'))), $this->getShopwareContext())->first();
                $oShopProduct = is_object($oShopwareProduct) ? $oShopwareProduct : null;
            }
            $this->oProduct = $oShopProduct;
            $aData = $this->get('shopdata');
            if (!isset($aData['variationObject'])) {
                if (isset($aData['variation_id'])) {
                    $aData['variationObject'] = $this->getShopwareProduct($aData['variation_id']);
                } else {
                    $aData['variationObject'] = $this->oProduct;
                }
            }
            $this->prepareProductForMarketPlace();
            if ($this->get('parentid') != 0) {//is variant
                $this->loadByShopProduct($oShopProduct, $this->get('parentid'), $aData);
            }
        }
        return $this;
    }

    protected function prepareProductForMarketPlace() {
        try {
            $aConfig = MLModule::gi()->getConfig();
            $iLangId = $aConfig['lang'];
            $currency = $this->getCurrencyWithISO($aConfig['currency']);

            try {
                if (Uuid::isValid($iLangId)) {
                    $this->oShopwareContext = MLShopware6Alias::getContext($iLangId, $currency->getId());
                }
            } catch (\Exception $ex) {
                MLMessage::gi()->addDebug($ex);
            }
            if ($this->oShopwareContext === null) {
                $sCurrentController = MLRequest::gi()->get('controller');
                if ($sCurrentController !== null && strpos($sCurrentController, ':') !== false) {
                    MLHttp::gi()->redirect(MLHttp::gi()->getUrl(array(
                        'controller' => substr($sCurrentController, 0, strpos($sCurrentController, '_')).'_config_prepare'
                    )));
                }
            }


        } catch (Exception $oExc) {
            MLMessage::gi()->addDebug($oExc);
        }

    }

    /**
     * @return $this
     * @throws MLAbstract_Exception
     */
    protected function loadShopVariants() {
        $iVariationCount = $this->getVariantCount();

        if ($iVariationCount > MLSetting::gi()->get('iMaxVariantCount')) {
            $this
                ->set('data', array('messages' => array(MLI18n::gi()->get('Productlist_ProductMessage_sErrorToManyVariants', array('variantCount' => $iVariationCount, 'maxVariantCount' => MLSetting::gi()->get('iMaxVariantCount'))))))
                ->save();
            MLMessage::gi()->addObjectMessage($this, MLI18n::gi()->get('Productlist_ProductMessage_sErrorToManyVariants', array('variantCount' => $iVariationCount, 'maxVariantCount' => MLSetting::gi()->get('iMaxVariantCount'))));
        } else {
            $criteriaIVariantCount = new Criteria();
            $criteriaIVariantCount->addAssociations(['options', 'options.group', 'options.option']);
            //Add variation attributes to the 'product.repository'
            $criteriaIVariantCount->addFilter(new EqualsFilter('product.parentId', $this->getMasterProductEntity()->getId()));
            /** @var ProductCollection $oVariantProductEntities */
            $oVariantProductEntities = MLShopware6Alias::getRepository('product')->search($criteriaIVariantCount, $this->getShopwareContext())->getEntities();
            $oVariantProductEntities->sort(
                function (ProductEntity $p1, ProductEntity $p2) {
                    if($p1->getId() !== $p2->getId()) {
                        $options1 = $p1->getOptions();
                        $options1->sort(function (PropertyGroupOptionEntity $op1, PropertyGroupOptionEntity $op2) {
                            return $op1->getGroup()->getId() > $op2->getGroup()->getId();
                        });
                        $options2 = $p2->getOptions();
                        $options2->sort(function (PropertyGroupOptionEntity $op1, PropertyGroupOptionEntity $op2) {
                            return $op1->getGroup()->getId() > $op2->getGroup()->getId();
                        });
                        foreach ($p1->getOptions() as $option1) {
                            foreach ($p2->getOptions() as $option2) {
                                if ($option2->getGroup()->getId() === $option1->getGroup()->getId()) {
//if (MLSetting::gi()->blDebug) !Kint::dump($option2->getGroup()->getName());
                                    if ($option1->getId() != $option2->getId()) {
                                        if ($option1->getPosition() === $option2->getPosition()) {
//if (MLSetting::gi()->blDebug) !Kint::dump($option1->getName(), $option2->getName(), $option1->getName() > $option2->getName());
                                            return $option1->getName() > $option2->getName();
                                        } else {
//if (MLSetting::gi()->blDebug) !Kint::dump($option1->getPosition(), $option2->getPosition(), $option1->getPosition() > $option2->getPosition());

                                            return $option1->getPosition() > $option2->getPosition();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            );
            $aVariant['variationObject'] = $this->oProduct;
            if ($this->oProduct->getChildCount() == 0) {
                $this->addVariant(
                    MLProduct::factory()->loadByShopProduct($this->oProduct, $this->get('id'), $aVariant)
                );
            }
            //$aInfo = [];
            foreach ($oVariantProductEntities as $oVariantProductEntity) {
               // foreach ($oVariantProductEntity->getOptions() as $option) {
               //     $aInfo[$oVariantProductEntity->getProductNumber()][$option->getGroup()->getName() . '-' . $option->getName()] = $option->getPosition();
                //}
                $aVariant = array();
                $aVariant['variation_id'] = $oVariantProductEntity->getId();
                $aVariant['variationObject'] = $oVariantProductEntity;
                if (isset($aVariant['variation_id'])) {
                    $this->addVariant(
                        MLProduct::factory()->loadByShopProduct($this->oProduct, $this->get('id'), $aVariant)
                    );
                }
            }
//            if (MLSetting::gi()->blDebug) {
//                !Kint::dump($aInfo);
//            }
        }
        return $this;
    }

    /**
     * @param ProductEntity $mProduct
     * @param int $iParentId parent id in magnalister_products
     * @param null $mData
     * @return $this|mixed
     * @throws Exception
     */
    public function loadByShopProduct($mProduct, $iParentId = 0, $mData = null) {
        $this->oProduct = $mProduct;
        try {
            $this->loadImageMediaObjects($this->oProduct);
        } catch (\Throwable $ex) {
            MLMessage::gi()->addDebug($ex);
        }

        /* for product who have no reference number ,random reference is inserted because it will made problem when product key is Article number */
        $sKey = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.keytype')->get('value');
        if ($sKey == 'pID') {
            $sProductKeyField = 'marketplaceidentid';
        } else {
            //here we use productssku instead of marketplaceidentsku, because if product was single product and now it has several variants we can find old product with productssku
            $sProductKeyField = 'productssku';
        }
        $this->aKeys = array($sProductKeyField, 'parentid');
        $this->set('parentid', $iParentId);
        $sMessage = array();
        if ($iParentId == 0) {
            $this
                ->set('marketplaceidentid', $this->oProduct->getId())
                ->set('marketplaceidentsku', $this->oProduct->getProductNumber())
                ->set('productsid', $this->oProduct->getId())
                ->set('productssku', $this->oProduct->getProductNumber())
                ->set('shopdata', array())
                ->set('data', array('messages' => $sMessage))
                ->save()
                ->aKeys = array('id');
            $this->prepareProductForMarketPlace();
        } else {
            $oVariation = $mData['variationObject'];
            unset($mData['variationObject']);
            $this
                ->set('marketplaceidentid', $oVariation->getId().'_'.$this->oProduct->getId())
                ->set('marketplaceidentsku', $oVariation->getProductNumber())
                ->set("productsid", $oVariation->getId())
                ->set("productssku", $oVariation->getProductNumber())
                ->set('shopdata', $mData)
                ->set('data', array('messages' => $sMessage));
            if ($this->exists()) {
                $this->aKeys = array('id');
                $this->save();
            } else {
                $this->save()->aKeys = array('id');
            }


        }
        return $this;
    }

    protected function loadImageMediaObjects($oProduct){
        $allImageMediaObjects = [];

        $productMediaCollection = $oProduct->getMedia();
        if ($productMediaCollection !== null && is_array($productMediaCollection)) {
            foreach ($productMediaCollection as $productMedia) {
                $media = $productMedia->getMedia();
                if ($media && str_starts_with($media->getMimeType(), 'image/')) {
                    $url = $media->getUrl(); // could be absolute or relative
                    $relativePath = parse_url($url, PHP_URL_PATH);
                    // Step 2: Remove '/public' prefix if present
                    if (str_starts_with($relativePath, '/public/')) {
                        $relativePath = substr($relativePath, 7); // remove exactly "/public"
                    }
                    $allImageMediaObjects[$relativePath] = $media;
                }
            }
        }

        MLImage::gi()->setImageMediaObjects($allImageMediaObjects);
    }

    static protected $createdObjectCache = array();


    /**
     * @param $sSku
     * @return array
     * @todo to be test with product-id
     *  if there some problems, see getByMarketplaceSKU method of magneto_model_product
     */
    public function createModelProductByMarketplaceSku($sSku) {
        $aOut = array('master' => null, 'variant' => null);
        $oMyTable = MLProduct::factory();
        /* @var $oMyTable ML_Shopware6_Model_Product */
        $oShopProduct = null;
        if (MLDatabase::factory('config')->set('mpid', 0)->set('mkey', 'general.keytype')->get('value') == 'pID') {
            $sIdent = 'marketplaceidentid';
            if (strpos($sSku, '_') !== false) {
                $aIds = explode("_", $sSku);
                if (is_numeric($aIds[1])) {
                    $oShopProductIDCriteria = new Criteria();
                    $oShopProduct = MLShopware6Alias::getRepository('product')->search($oShopProductIDCriteria->addFilter(new EqualsFilter('product.id', $aIds[1])), $this->getShopwareContext())->first();
                }
            } else if (is_numeric($sSku)) {
                $oShopProductSKUCriteria = new Criteria();
                $oShopProduct = MLShopware6Alias::getRepository('product')->search($oShopProductSKUCriteria->addFilter(new EqualsFilter('product.productNumber', $sSku)), $this->getShopwareContext())->first();
            }
        } else {
            $sIdent = 'marketplaceidentsku';
            $criteria = new Criteria();
            $oArticleDetail = MLShopware6Alias::getRepository('product')
                ->search($criteria->addFilter(new EqualsFilter('product.productNumber', $sSku)), $this->getShopwareContext())
                ->first();

            if ($oArticleDetail !== null) {
                if ($oArticleDetail->getParentId() === null) {
                    $oShopProduct = $oArticleDetail;
                } else {
                    $sProductId = $oArticleDetail->getParentId();
                    $oShopProduct = MLShopware6Alias::getRepository('product')
                        ->search(
                            (new Criteria())->addFilter(new EqualsFilter('product.id', $sProductId)),
                            $this->getShopwareContext())
                        ->first();
                }
            }
        }

        try {
            if ($oShopProduct != null && is_object($oShopProduct)) {

                $iShopProductId = $oShopProduct->getId();
                if (!isset(self::$createdObjectCache[$iShopProductId])) {

                    $oMyTable->loadByShopProduct($oShopProduct);

                    $oMyTable->getVariants();

                    self::$createdObjectCache[$iShopProductId]['master'] = $oMyTable;

                    foreach ($oMyTable->getVariants() as $oVariant) {
                        self::$createdObjectCache[$iShopProductId]['variants'][$oVariant->get($sIdent)] = $oVariant;
                    }
                }

                $marketplaceSku = self::$createdObjectCache[$iShopProductId]['master']->get($sIdent);
                if ($marketplaceSku === $sSku
                    || ( // if migration used check for M add end of SKU and replace it again with _Master to get correct result
                        $sIdent === 'marketplaceidentsku'
                        && MLModule::gi()->getConfig('shopware6.master.sku.migration.options') === '1'
                        && ((($iLastIndex = strlen((string)$marketplaceSku) - 7) > 0 && substr($marketplaceSku, $iLastIndex) === '_Master') ? substr($marketplaceSku, 0, $iLastIndex).'M' : $marketplaceSku) === $sSku
                    )
                ) {
                    $aOut['master'] = self::$createdObjectCache[$iShopProductId]['master'];
                }
                //moein question: the $sSku is master product sku but here my variant has a different sku, and it never runs the condition

                if (isset(self::$createdObjectCache[$iShopProductId]['variants'][$sSku])) {
                    $aOut['variant'] = self::$createdObjectCache[$iShopProductId]['variants'][$sSku];
                }
            }
        } catch (Exception $ex) {
            MLMessage::gi()->addDebug($ex);
        }
        return $aOut;
    }

    /**
     * @param string $sSku
     * @param bool $blMaster
     * @return $this|ML_Shop_Model_Product_Abstract|ML_Shopware_Model_Product
     */
    public function getByMarketplaceSKU($sSku, $blMaster = false) {
        if ($blMaster && substr($sSku, -7) === '_Master') {
            // replace _Master with M - for shopware 5 to 6 migrations they extend it with an M
            $sSku = substr($sSku, 0, strrpos($sSku, '_Master')).'M';
        }
        if (empty($sSku)) {
            return $this->set('id', 0);
        } else {
            $aCreated = $this->createModelProductByMarketplaceSku($sSku);
            $this->init(true);
            if ($aCreated[$blMaster ? 'master' : 'variant'] instanceof ML_Shop_Model_Product_Abstract) {
                $this->set('id', $aCreated[$blMaster ? 'master' : 'variant']->get('id'));
            }
            return $this;
        }
    }

    /**
     * @return string
     */
    public function getShortDescription() {
        $this->load();
        return $this->getProductFieldValue('MetaDescription', false);
    }

    /**
     * @return string|string[]|null
     */
    public function getDescription() {
        $this->load();
        return $this->getProductFieldValue('Description');
    }



    /**
     * Return a url to edit product in admin panel of Shopware 6
     * @return string
     * @throws Exception
     */
    public function getEditLink(): string {
        $this->load();
        return MLShopware6Alias::getHttpModel()->getAdminUrl().'#/sw/product/detail/'.$this->getCorrespondingProductEntity()->getId();
    }

    /**
     * return product front url
     * @return string
     */
    public function getFrontendLink() {
        $this->load();
        if ($this->get('parentid') == 0) {
            return MagnalisterController::getShopwareRequest()->server->get('APP_URL').'/detail/'.$this->getMasterProductEntity()->getId();
        } else {
            return MagnalisterController::getShopwareRequest()->server->get('APP_URL').'/detail/'.$this->get('productsid');
        }
    }


    /**
     * @param int $iX
     * @param int $iY
     * @return array|string
     */
    public function getImageUrl($iX = 40, $iY = 40) {
        $this->load();
        try {
            if ($this->getCorrespondingProductEntity()->getParentId() == null) {
                //Parent Product
                $ProductMediaId = $this->getCorrespondingProductEntity()->getCoverId();
            } else {
                //Variation
                if ($this->getCorrespondingProductEntity()->getCoverId() === null) {
                    //Replacing the Variation cover image by Parent product image if the  Variation cover image  is null
                    $ProductMediaId = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId())->getCoverId();
                } else {
                    // Variation cover image
                    $ProductMediaId = $this->getCorrespondingProductEntity()->getCoverId();
                }
            }

            if ($ProductMediaId !== null) {
                $criteriaPM = new Criteria();
                $PM = MagnalisterController::getShopwareMyContainer()->get('product_media.repository')->search($criteriaPM->addFilter(new EqualsFilter('id', $ProductMediaId)), $this->getShopwareContext())->first();

                if ($PM !== null) {
                    $criteria = new Criteria();
                    $oMedia = MagnalisterController::getShopwareMyContainer()->get('media.repository')->search($criteria->addFilter(new EqualsFilter('id', $PM->getMediaId())), $this->getShopwareContext())->first();
                    $aImg = $oMedia->getUrl();

                    try {
                        $aMediaCache = MLSetting::gi()->get('oShopwareMediaCache', array());
                    } catch (Exception $ex) {
                        $aMediaCache = array();
                    }
                    $aMediaCache[$aImg] = $oMedia;
                    MLSetting::gi()->set('oShopwareMediaCache', $aMediaCache, true);

                    $sImagePath = $aImg;
                } else {
                    $sImagePath = false;
                }
            } else {
                $sImagePath = false;
            }
            if ($sImagePath !== false) {
                return MLImage::gi()->resizeImage($sImagePath, 'products', $iX, $iY, true);
            } else {
                return '';
            }
        } catch (Exception $oEx) {
            return '';
        }
    }

    /**
     * function to get data and put to cache
     *
     * @param $criteria Criteria
     * @param $media
     * @return \Shopware\Models\Media\Repository
     */
    public function getMediaUrlInMediaRepositoryById($criteria, $media) {
        $oMedia = null;
        $imgUrl = null;
        // get the image list previously cached while loading the product in loadByShopProduct function
        $aProductImageObjects = MLImage::gi()->getImageMediaObjects();
        foreach ($aProductImageObjects as $aProductImageObject) {
            if ($media->getMediaId() === $aProductImageObject->getId()) {
                $oMedia = $aProductImageObject;
                $imgUrl = $oMedia->getUrl();
            }
        }

        if (!isset($oMedia) || !isset($imgUrl)) {
            $oMedia = MagnalisterController::getShopwareMyContainer()
                ->get('media.repository')
                ->search(
                    $criteria->addFilter(
                        new EqualsFilter('id', $media->getMediaId())
                    ), $this->getShopwareContext()
                )->first();
            $imgUrl = $oMedia->getUrl();
        }


        try {
            $aMediaCache = MLSetting::gi()->get('oShopwareMediaCache', array());
        } catch (Exception $ex) {
            $aMediaCache = array();
        }
        $aMediaCache[$imgUrl] = $oMedia;
        MLSetting::gi()->set('oShopwareMediaCache', $aMediaCache, true);

        return $imgUrl;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getImages() {
        if ($this->aImages === null) {
            $this->load();
            $aImgs = array();
            $variationImgs = array();

            if ($this->get('parentid') == 0) {
                //Parent Product
                $productMediacriteria = new Criteria();
                $productMediacriteria->addFilter(new EqualsFilter('productId', $this->getCorrespondingProductEntity()->getId()));
                $productMediacriteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
                $productMedia = MagnalisterController::getShopwareMyContainer()->get('product_media.repository')->search($productMediacriteria, $this->getShopwareContext())->getEntities();
                $CoverImage = array();
                foreach ($productMedia as $media) {
                    if ($media->getId() == $this->getCorrespondingProductEntity()->getCoverId()) {
                        $criteriaCoverImage = new Criteria();
                        $CoverImage[] = $this->getMediaUrlInMediaRepositoryById($criteriaCoverImage, $media);
                    } else {
                        $criteria = new Criteria();
                        $aImgs[] = $this->getMediaUrlInMediaRepositoryById($criteria, $media);
                    }
                }
                //Collect all variations picture and merge it with master product
                $VariationListcriteria = new Criteria();
                $VariationList = MLShopware6Alias::getRepository('product')
                    ->search($VariationListcriteria->addFilter(new EqualsFilter('parentId', $this->getCorrespondingProductEntity()->getId())), $this->getShopwareContext())->getEntities();
                if ($VariationList->getElements()) {
                    foreach ($VariationList->getElements() as $value) {
                        $VariationMediacriteria = new Criteria();
                        $VariationMediacriteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
                        $VariationMedia = MLShopware6Alias::getRepository('product_media')->search($VariationMediacriteria->addFilter(new EqualsFilter('productId', $value->getId())), $this->getShopwareContext())->getEntities();

                        foreach ($VariationMedia as $media) {
                            //Find the cover image of variation and add it in the first place
                            foreach ($VariationMedia as $media2) {
                                $vmcriteria2 = new Criteria();
                                if ($value->getCoverId() == $media2->getId()) {
                                    $variationImgs[] = $this->getMediaUrlInMediaRepositoryById($vmcriteria2, $media2);
                                }
                            }
                            $vmcriteria = new Criteria();
                            if ($value->getCoverId() == $media->getId()) {
                                //escape the cover image because with add the cover image for variation on the foreach above
                            } else {
                                $variationImgs[] = $this->getMediaUrlInMediaRepositoryById($vmcriteria, $media);
                            }
                        }
                    }
                }
                $aImgs = array_merge($CoverImage, $aImgs, $variationImgs);
            } else {
                //variations Images
                $productMediacriteria = new Criteria();
                $productMediacriteria->addFilter(new EqualsFilter('productId', $this->getCorrespondingProductEntity()->getId()));
                $productMediacriteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
                $productMedia = MagnalisterController::getShopwareMyContainer()->get('product_media.repository')->search($productMediacriteria, $this->getShopwareContext())->getEntities();
                $CoverImage = array();
                //If variation images is not inherited and the variation images has been set
                if (!empty($productMedia->getElements())) {
                    foreach ($productMedia as $media) {
                        if ($media->getId() == $this->getCorrespondingProductEntity()->getCoverId()) {
                            $criteriaCoverImage = new Criteria();
                            $CoverImage[] = $this->getMediaUrlInMediaRepositoryById($criteriaCoverImage, $media);
                        }
                        $criteria = new Criteria();
                        $aImgs[] = $this->getMediaUrlInMediaRepositoryById($criteria, $media);
                    }
                } else {
                    //If variation images is inherited and the variation is empty then it will fill by parent product images
                    $productParentMediacriteria = new Criteria();
                    $productParentMediacriteria->addFilter(new EqualsFilter('productId', $this->getCorrespondingProductEntity()->getParentId()));
                    $productParentMediacriteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
                    $productParentMedia = MagnalisterController::getShopwareMyContainer()->get('product_media.repository')->search($productParentMediacriteria, $this->getShopwareContext())->getEntities();
                    foreach ($productParentMedia as $media) {
                        if ($media->getId() == $this->getCorrespondingProductEntity()->getCoverId()) {
                            $criteriaParentCoverImage = new Criteria();
                            $CoverImage[] = $this->getMediaUrlInMediaRepositoryById($criteriaParentCoverImage, $media);
                        }
                        $criteria = new Criteria();
                        $aImgs[] = $this->getMediaUrlInMediaRepositoryById($criteria, $media);
                    }
                }
                $aImgs = array_merge($CoverImage, $aImgs);
            }
            $this->aImages = array_unique($aImgs);
        }

        return $this->aImages;
    }

    /**
     * @return string
     */
    public function getMetaDescription() {
        return $this->getShortDescription();
    }

    /**
     * @return string
     */
    public function getMetaKeywords() {
        $this->load();
        $sKeywords = $this->getProductFieldValue('CustomSearchKeywords');
        if (is_array($sKeywords)) {
             $sKeywords = implode(',', $sKeywords);
        }
        return $sKeywords;
    }

    /**
     * @param $sFieldName
     * @param bool $blGeneral
     * @return \Doctrine\ORM\PersistentCollection|int|string|null
     * @throws Exception
     */
    public function getModulField($sFieldName, $blGeneral = false, $blMultiValue = false) {
        $this->load();
        if ($blGeneral) {
            $sField = MLDatabase::factory('config')->set('mpid', 0)->set('mkey', $sFieldName)->get('value');
        } else {
            $sField = MLModule::gi()->getConfig($sFieldName);
        }
        $sField = empty($sField) ? $sFieldName : $sField;

        return $this->getProductField($sField, $blMultiValue);
    }

    /**
     *
     * @var ProductEntity
     */
    protected $oRealProduct = null;

    /**
     * Check if magnalister product is a variation (MarketplaceIdentId contains "_") then it return ProductEntity object of variation
     * otherwise it returns ProductEntity object of master product($this->oProduct)
     * @return ProductEntity
     * @throws Exception
     */
    public function getCorrespondingProductEntity($oShopwareContext = null): ?ProductEntity {
        $oShopwareContext = $oShopwareContext === null ? $this->getShopwareContext() : $oShopwareContext;
        if ($this->oProduct === null) {
            $this->load();
        }

        if ($this->oRealProduct === null) {
            $sku = $this->get('MarketplaceIdentId');
            if (strpos($sku, '_') !== false) {
                //Checked if it is a variation then it fills the "$oProduct" with variation product object .
                $oProduct = $this->getShopwareProduct($this->get('productsid'), $oShopwareContext);
            } else {
                //Else it is a Master product then it fill the "$oProduct" with master product object ."$this->oProduct" is master product object.
                $oProduct = $this->oProduct;
            }
            if (!is_object($oProduct)) {
                MLMessage::gi()->addDebug('Product doesn\'t exist . SKU : '.$this->get('productssku'));
                $this->delete(); //delete from magnalister_product
                throw new Exception;
            }
            $this->oRealProduct = $oProduct;
            return $oProduct;
        } else {
            return $this->oRealProduct;
        }
    }


    /**
     * @return string
     */
    public function getName() {
        $this->load();
        $attribute = '';
        $optionsentity = MagnalisterController::getShopwareMyContainer()->get('property_group_option.repository');
        if ($this->getCorrespondingProductEntity()->getOptionIds() !== null) {//add variation which have price and configuration option
            $attribute = $this->getVariantNamePostfix($optionsentity, $attribute);
        }
        if ($this->getCorrespondingProductEntity()->getName() == null && $this->getCorrespondingProductEntity()->getParentId() !== null) {
            $oProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId());
            if ($oProduct->getName() !== NULL) {
                $sMasterProductName = $oProduct->getName().$attribute;
            } else {
                $oProduct3 = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId(), Context::createDefaultContext());
                $sMasterProductName = $oProduct3->getName().$attribute;
            }
        } elseif ($this->getCorrespondingProductEntity()->getName() == null && $this->getCorrespondingProductEntity()->getParentId() == null) {
            $oProduct2 = $this->getCorrespondingProductEntityWithDefault();
            $sMasterProductName = $oProduct2->getName().$attribute;
        } else {
            $sMasterProductName = $this->getCorrespondingProductEntity()->getName().$attribute;
        }

        return $sMasterProductName;
    }

    /**
     * @param bool $blGros
     * @param bool $blFormated
     * @return mixed
     * @throws Enlight_Exception
     */
    public function getShopPrice($blGros = true, $blFormated = false) {
        $this->load();
        $this->blIsMarketplacePrice = false;
        $mReturn = $this->getPrice($blGros, $blFormated/* ,false */);
        return $mReturn;
    }

    /**
     *
     * @param \ML_Shop_Model_Price_Interface $oPrice
     * @param bool $blGros
     * @param bool $blFormated
     * @return mixed
     *
     * @throws Exception
     * @todo check price groups for special price
     */
    public function getSuggestedMarketplacePrice(\ML_Shop_Model_Price_Interface $oPrice, $blGros = true, $blFormated = false) {
        $this->load();
        $aConf = $oPrice->getPriceConfig();
        $this->blIsMarketplacePrice = true;
        $this->sPriceRules = $aConf['group'];
        $fTax = $aConf['tax'];
        $sPriceKind = $aConf['kind'];
        $fPriceFactor = (float)$aConf['factor'];
        $iPriceSignal = $aConf['signal'];
        $this->blDiscountMode = $aConf['special'];
        $mReturn = $this->getPrice($blGros, $blFormated, $sPriceKind, $fPriceFactor, $iPriceSignal, $fTax);
        return $mReturn;
    }

    /**
     * @param $blGros
     * @param $blFormated
     * @param string $sPriceKind
     * @param float $fPriceFactor
     * @param null $iPriceSignal
     * @param null $fTax
     * @return mixed
     * @throws Exception
     * @see  \Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator::getCurrencyPrice Shopware 6.4
     * @see  \Shopware\Core\Content\Test\Product\SalesChannel\ProductPriceDefinitionBuilderTest Shopware 6.3
     */
    protected function getPrice($blGros, $blFormated, $sPriceKind = '', $fPriceFactor = 0.0, $iPriceSignal = null, $fTax = null) {
        /** @var CurrencyEntity $CurrencyObject */
        $CurrencyObject = MLShopware6Alias::getRepository('currency')
            ->search((new Criteria([$this->getShopwareContext()->getCurrencyId()])), $this->getShopwareContext())->first();
        $oProductEntity = $this->getCorrespondingProductEntity();
        $advancedPrice = array();
        if ($this->blIsMarketplacePrice) {
            $advancedPrice = $this->getAdvancedPrice($oProductEntity, $advancedPrice, $CurrencyObject);
        }
        $price = $oProductEntity->getPrice();
        if ($price === null) {//price is inherited from parent product
            $oProductEntity = $this->getShopwareProduct($oProductEntity->getParentId());
        }

        if ($oProductEntity->getTaxId() === null) {
            //Variations of a "Parent Product" don't have a TaxId and the Tax Id has been stored in "Parent Product". Getting the "Parent Product Id" from Variations via "$oProductEntity->getParentId()" and put it on the "product.repository" search section to get the "Parent Product" object with Tax Id.
            $oProductTaxID = $this->getShopwareProduct($oProductEntity->getParentId());
            $oProductEntity->setTaxId($oProductTaxID->getTaxId());
        }

        if (version_compare(MLSHOPWAREVERSION, '6.4.0.0', '>=')) {
            list($fBrutPrice, $fNetPrice) = $this->getShopware64Price($oProductEntity, $advancedPrice, $CurrencyObject);
        } else {//Shopware 6.3
            list($fBrutPrice, $fNetPrice) = $this->getShopware63Price($oProductEntity, $advancedPrice, $CurrencyObject);
        }

        $oPrice = MLPrice::factory();
        if ($fTax !== null) {
            $fBrutPrice = $oPrice->calcPercentages(null, $fNetPrice, $fTax);
        }

        // Marketplace Configuration - Addition or percentage price change#
        if ($sPriceKind === 'percent') {
            $fBrutPrice = $oPrice->calcPercentages(null, $fBrutPrice, $fPriceFactor);
            $fNetPrice = $oPrice->calcPercentages(null, $fNetPrice, $fPriceFactor);
        } elseif ($sPriceKind === 'addition') {
            $fBrutPrice = $fBrutPrice + $fPriceFactor;
            $fNetPrice = $fNetPrice + $fPriceFactor;
        }

        if ($iPriceSignal !== null) {
            //If price signal is single digit then just add price signal as last digit
            if (strlen((string)$iPriceSignal) == 1) {
                $fBrutPrice = (0.1 * (int)($fBrutPrice * 10)) + ((int)$iPriceSignal / 100);
                $fNetPrice = (0.1 * (int)($fNetPrice * 10)) + ((int)$iPriceSignal / 100);
            } else {
                $fBrutPrice = ((int)$fBrutPrice) + ((int)$iPriceSignal / 100);
                $fNetPrice = ((int)$fNetPrice) + ((int)$iPriceSignal / 100);
            }
        }

        $fPrice = round($blGros ? $fBrutPrice : $fNetPrice, 2);
        $fPrice = $this->priceAdjustment($fPrice);
        if (
            $this->getCorrespondingProductEntity()->getParentId() !== null //price of master product is not important
            && (float)$fPrice === 0.00
        ) {
            throw new Exception(MLI18n::gi()->get('Productlist_ProductMessage_NotAllPricesValid'));
        }
        if ($blFormated) {
            $fPrice = MLShopware6Alias::getPriceModel()->format($fPrice, MLModule::gi()->getConfig('currency'), false);
        }
        return $fPrice;
    }

    public function priceAdjustment($fPrice) {
        return $fPrice;
    }

    public function getSalesChannel() {
        //Get sale channel from order configuration
        $iSalesChannelId = MLModule::gi()->getConfig('orderimport.shop');
        /** @var $oSalesChannel \Shopware\Core\System\SalesChannel\SalesChannelEntity|null */
        $oSalesChannel = null;
        if ($iSalesChannelId !== null) {
            $oCriteria = new Criteria(['id' => $iSalesChannelId]);
            $oCriteria->addAssociation('type');
            $oSalesChannel = MLShopware6Alias::getRepository('sales_channel')
                ->search($oCriteria, $this->getShopwareContext())->first();
            if ($oSalesChannel === null || $oSalesChannel->getType()->getIconName() !== 'default-building-shop') {
                $oCriteria = new Criteria();
                $oCriteria->addAssociation('type');
                $oCriteria->addSorting(new FieldSorting('active', 'DESC'));
                $oRepository = MLShopware6Alias::getRepository('sales_channel')
                    ->search($oCriteria, $this->getShopwareContext());
                $oSalesChannelStoreFront = null;
                foreach ($oRepository->getEntities() as $oItem) {
                    if ($oItem->getType()->getIconName() === 'default-building-shop') {//trying to search storefront sale channel
                        $oSalesChannelStoreFront = $oItem;
                        break;
                    }
                }
                $oSalesChannel = ($oSalesChannelStoreFront === null ? $oRepository->first() : $oSalesChannelStoreFront);
            }
        }
        return $oSalesChannel === null ? $iSalesChannelId : $oSalesChannel->getId();
    }

    /**
     * return master ProductEntity of Shopware
     * @return ProductEntity
     */
    public function getMasterProductEntity() {
        if ($this->oProduct === null) {
            $this->load();
        }
        return $this->oProduct;
    }


    /**
     * @inheritDoc
     */
    public function getTax($aAddressSets = null) {
        try {
            $oShopwareProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getId(), null, ['tax.rules.tax', 'tax.rules.type']);
            if ($oShopwareProduct->getTax() === null && $oShopwareProduct->getParentId() != null) {
                $oShopwareProduct = $this->getShopwareProduct($oShopwareProduct->getParentId(), null, ['tax.rules.tax', 'tax.rules.type']);
            }
            $fTax = $oShopwareProduct->getTax()->getTaxRate();

            if ($aAddressSets !== null) {
                $aAddressData = $aAddressSets['Shipping'];
                $countryCriteria = new Criteria();
                $countryCriteria->addFilter(new EqualsFilter('country.iso', $aAddressData['CountryCode']));
                $oCountry = MLShopware6Alias::getRepository('country')
                    ->search($countryCriteria, $this->getShopwareContext())->first();
                if (is_object($oCountry)) {//when country exist or when country doesn't have any area, we cannot use address to calculate tax, we return normal tax
                    foreach ($oShopwareProduct->getTax()->getRules()->filterByProperty('countryId', $oCountry->getId()) as $taxRuleEntity) {
                        /** @var $taxRuleEntity TaxRuleEntity */
                        $zipCode = $aAddressData['Postcode'] ?? 0;
                        $ProductTaxTypeTechnicalName = $taxRuleEntity->getType()->getTechnicalName();
                        switch ($ProductTaxTypeTechnicalName) {
                            case ZipCodeRangeRuleTypeFilter::TECHNICAL_NAME:
                                $toZipCode = isset($taxRuleEntity->getData()['toZipCode']) ? $taxRuleEntity->getData()['toZipCode'] : null;
                                $fromZipCode = isset($taxRuleEntity->getData()['fromZipCode']) ? $taxRuleEntity->getData()['fromZipCode'] : null;
                                if (
                                    ($fromZipCode !== null && $toZipCode !== null && $zipCode > $fromZipCode && $zipCode < $toZipCode)
                                    ||
                                    ($fromZipCode !== null && $toZipCode === null && $zipCode > $fromZipCode)
                                    ||
                                    ($fromZipCode === null && $toZipCode !== null && $zipCode < $toZipCode)
                                ) {
                                    $selectedTaxRule = $taxRuleEntity;
                                    break 2;
                                }
                                break;
                            case ZipCodeRuleTypeFilter::TECHNICAL_NAME:
                                if (isset($taxRuleEntity->getData()['zipCode']) && $taxRuleEntity->getData()['zipCode'] === $zipCode) {
                                    $selectedTaxRule = $taxRuleEntity;
                                    break 2;
                                }
                                break;
                            case IndividualStatesRuleTypeFilter::TECHNICAL_NAME:
                                if (array_key_exists('Suburb', $aAddressData) && !empty($aAddressData['Suburb'])) {
                                    $countryStateCriteria = new Criteria();
                                    /** @var CountryStateEntity $countryState */
                                    $countryState = MLShopware6Alias::getRepository('country_state')
                                        ->search($countryStateCriteria->addFilter(new EqualsFilter('name', $aAddressData['Suburb'])), $this->getShopwareContext())->first();

                                    if ($countryState !== null) {
                                        $stateId = $countryState->getId();
                                        $states = $taxRuleEntity->getData()['states'];
                                        if (\in_array($stateId, $states, true)) {
                                            $selectedTaxRule = $taxRuleEntity;
                                            break 2;
                                        }
                                    }
                                }
                                break;
                            default ://set default country rule
                                $selectedTaxRule = $taxRuleEntity;
                                break;
                        }
                    }
                    if (isset($selectedTaxRule) && $selectedTaxRule) {
                        $fTax = $selectedTaxRule->getTaxRate();
                    }
                }
            }

        } catch (\Exception $ex) {
            echo($ex->getMessage().$ex->getFile().$ex->getLine().$ex->getTraceAsString());
            $fTax = MLModule::gi()->getConfig('mwstfallback');
        }

        return $fTax;
    }

    /**
     * @return int
     */
    public function getTaxClassId() {
        if ($this->getCorrespondingProductEntity()->getTax() !== null) {//if it is single product
            return $this->getCorrespondingProductEntity()->getTax()->getId();
        } else if ($this->getMasterProductEntity()->getTax() !== null) {//if it is variant tax should be got from master product
            return $this->getMasterProductEntity()->getTax()->getId();
        }
        return null;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getStock() {
        if ($this->get('parentid') == '0') {
            return MLHelper::gi('model_product')->getTotalCount($this->getMasterProductEntity());
        } else {
            return $this->getCorrespondingProductEntity()->getAvailableStock();
        }
    }

    /**
     * @param string $sType
     * @param float $fValue
     * @param null $iMax
     * @return int
     * @throws Exception
     */
    public function getSuggestedMarketplaceStock($sType, $fValue, $iMax = null) {
        if (
            MLModule::gi()->getConfig('inventar.productstatus') == 'true' && !$this->isActive()
        ) {
            return 0;
        }
        if ($sType == 'lump') {
            $iStock = $fValue;
        } else {
            $iStock = $this->getStock();
            if ($sType == 'stocksub') {
                $iStock = $iStock - $fValue;
            }

            if (!empty($iMax)) {
                $iStock = min($iStock, $iMax);
            }
        }

        $iStock = (int)$iStock;
        return $iStock > 0 ? $iStock : 0;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getVariatonData() {
        return $this->getVariatonDataOptinalField(array('name', 'value'));
    }

    public function getPrefixedVariationData() {
        $variationData = $this->getVariatonDataOptinalField(array('name', 'value', 'code', 'valueid'));
        return $variationData;
    }

    /**
     * @param array $aFields
     * @return array
     * @throws Exception
     */
    public function getVariatonDataOptinalField($aFields = array()) {
        $oVariantProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getId());
        $optionsentity = MagnalisterController::getShopwareMyContainer()->get('property_group_option.repository');
        $groupentity = MagnalisterController::getShopwareMyContainer()->get('property_group.repository');

        $aOut = array();

        if (!$this->isSingle() && $oVariantProduct->getOptionIds() != null) {
            foreach ($oVariantProduct->getOptionIds() as $aAttribute) {
                $options = $optionsentity->search(new Criteria(['id' => $aAttribute]), $this->getShopwareContext())->first();
                // Product has options and an attribute but the option themselves doesn't exist
                if (!is_object($options)) {
                    continue;
                }
                $group = $groupentity->search(new Criteria(['id' => $options->getGroupId()]), $this->getShopwareContext())->first();
                if ($group->getName() == null) {
                    $group = $groupentity->search(new Criteria(['id' => $options->getGroupId()]), Context::createDefaultContext())->first();
                }
                $aData = array();
                if (in_array('code', $aFields)) {//an identifier for group of attributes , that used in Meinpaket at the moment
                    $aData['code'] = 'a_'.$group->getId();
                }
                if (in_array('valueid', $aFields)) {//an identifier for group of attributes , that used in Meinpaket at the moment
                    $aData['valueid'] = $options->getId();
                }
                if (in_array('name', $aFields)) {
                    $aData['name'] = $group->getName();
                }
                if (in_array('value', $aFields)) {
                    if ($options->getName() !== null) {
                        $aData['value'] = $options->getName();
                    } else {
                        $optionsReplace = $optionsentity->search(new Criteria(['id' => $aAttribute]), Context::createDefaultContext())->first();
                        $aData['value'] = $optionsReplace->getName();
                    }
                }
                $aOut[] = $aData;
            }
        }
        if (in_array('name', $aFields)) {
            usort($aOut, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }
        return $aOut;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isActive() {
        $mStatus = null;
        try {
            $mStatus = $this->getCorrespondingProductEntity()->getActive();
        } catch (\Throwable $ex) {
            // If corresponding product object is a variant and active status is inherited from parent,
            // in some versions of Shopware 6 "$this->getCorrespondingProductEntity()->getActive()" will throw a PHP Error, because of that used \Throwable
            // to catch any PHP error, as fallback we use active status of parent product
        }
        if ($mStatus === null) {
            $mStatus = $this->getMasterProductEntity()->getActive();
        }
        return $mStatus;
    }

    /**
     * This function is not used for Shopware, by importing order there is Shopware core function to reduce the stock
     * @param int $iStock
     */
    public function setStock($iStock) {

    }

    /**
     * @return mixed|string
     */
    public function getCategoryPath() {
        $category = MagnalisterController::getShopwareMyContainer()->get('category.repository');
        $aInvalidIds = $this->getRootCategoriesIds();

        $sCatPath = '';
        if ($this->getMasterProductEntity()->getCategoryTree() != null) {
            foreach ($this->getMasterProductEntity()->getCategoryTree() as $oCat) {
                $cat = $category->search(new Criteria(['id' => $oCat]), $this->getShopwareContext())->first();
                $sInnerCat = '';
                //this condition will remove root category
                if (!in_array($oCat, $aInvalidIds)) {
                    $sInnerCat = $cat->getName().'&nbsp;&gt;&nbsp;'.$sInnerCat;
                }
                $sCatPath .= $sInnerCat.'<br>';
            }
        }

        return $sCatPath;
    }

    /**
     * @param bool $blIncludeRootCats
     * @return array
     */
    public function getCategoryIds($blIncludeRootCats = true) {
        $aCategories = array();
        $aFilterCats = $blIncludeRootCats ? array() : $this->getRootCategoriesIds();
        if ($this->getMasterProductEntity()->getCategoryTree() != null) {
            foreach ($this->getMasterProductEntity()->getCategoryTree() as $oCat) {
                if (!in_array($oCat, $aFilterCats)) {
                    $aCategories[] = $oCat;
                }
            }
        }

        return $aCategories;
    }

    protected function getRootCategoriesIds() {
        $repository = MagnalisterController::getShopwareMyContainer()->get('category.repository');
        $criteriaCAT = new Criteria();
        $aResult = $repository->search($criteriaCAT->addFilter(new EqualsFilter('parentId', NULL)), $this->getShopwareContext())->getEntities();
        $invalidIds = array();
        foreach ($aResult as $id) {
            $invalidIds[] = $id->getId();
        }

        return $invalidIds;
    }

    /**
     * @param bool $blIncludeRootCats
     * @return array
     */
    public function getCategoryStructure($blIncludeRootCats = true) {
        $category = MagnalisterController::getShopwareMyContainer()->get('category.repository');
        $aCategories = array();
        $aInvalidIds = $aExistedCatId = $blIncludeRootCats ? array() : $this->getRootCategoriesIds();
        if (is_array($this->getMasterProductEntity()->getCategoryTree())) {
            foreach ($this->getMasterProductEntity()->getCategoryTree() as $oCat) {
                /* @var $oCat \Shopware\Models\Category\Category */
                $cat = $category->search(new Criteria(['id' => $oCat]), $this->getShopwareContext())->first();

                if (
                    in_array($cat->getId(), $aInvalidIds)
                    || in_array($cat->getId(), $aExistedCatId)
                ) {
                    break;
                }
                $sDescription = ($cat->getMetaDescription() === null) ? '' : $cat->getMetaDescription();
                $aCurrentCat = array(
                    'ID'          => $cat->getId(),
                    'Name'        => $cat->getName(),
                    'Description' => $sDescription,
                    'Status'      => true,
                );
                $aExistedCatId[] = $cat->getId();

                if ($cat->getParentId() !== null) {
                    $aCurrentCat['ParentID'] = $cat->getParentId();
                }
                $aCategories[] = $aCurrentCat;

            }
        }
        return $aCategories;
    }

    /**
     * @param $sName
     * @param null $sMethod
     * @return \Doctrine\ORM\PersistentCollection|int|string|null
     * @throws Exception
     */
    public function getProductField($sName, $blMultiValue = false) {
        $mValue = '';
        if (strpos($sName, 'a_') === 0) {
            $sName = substr($sName, 2);
            $mValue = $this->getProductFieldVariationAttribute($sName, $blMultiValue);
        } elseif (strpos($sName, 'p_') === 0) {
            $sName = substr($sName, 2);
            $mValue = $this->getProductFieldProperties($sName, $blMultiValue);
        } elseif (strpos($sName, 'c_') === 0) {
            $sName = substr($sName, 2);
            $mValue = $this->getProductFieldCustomField($sName, $blMultiValue);
        }
        elseif (strpos($sName, 'cm_') === 0) {
            $mValue = $this->getProductFieldManufacturerCustomField($sName, $mValue);

        }
        else if(strpos($sName, 'ListPrice_') === 0){
            $mValue = $this->getProductFieldListPrice($sName, $mValue);
        }
        else {
            if (method_exists($this->getCorrespondingProductEntity(), 'get'.$sName)) {
                $mValue = $this->getProductFieldModelField($sName, $mValue);
            } else if (strpos($sName, '_ValueWithUnit') !== false) {
                $aName = explode('_', $sName);
                if (isset($aName[0]) && in_array($aName[0], ['Width', 'Height', 'Length', 'Weight'])) {
                    $mValue = $this->getProductField($aName[0]).' '.$this->getProductField($aName[0].'_Unit');
                }
            } else if (strpos($sName, '_Unit') !== false) {
                $aName = explode('_', $sName);
                if (isset($aName[0])) {
                    if (in_array($aName[0], ['Width', 'Height', 'Length'])) {
                        $mValue = 'mm';
                    } else if ($aName[0] === 'Weight') {
                        $mValue = 'KG';
                    }
                }
            } else {
                MLMessage::gi()->addDebug('method get'.$sName.' does not exist in Article or ArticleDetails');
                return '';
            }
        }
        return $mValue;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getWeight() {
        $oDetail = $this->getCorrespondingProductEntity();
        $sWeight = (float)$oDetail->getWeight();
        if ($sWeight > 0) {
            return array(
                "Unit"  => "KG",
                "Value" => $sWeight,
            );
        } else {
            return array();
        }
    }

    /**
     * @param float $fPrice
     * @param bool $blLong
     * @return string
     * @throws Exception
     */
    public function getBasePriceString($fPrice, $blLong = true) {
        $aBasePrice = $this->getBasePrice();
        if (empty($aBasePrice)) {
            return '';
        } else {
            $fPrice = ((float)$fPrice / (float)$aBasePrice['ShopwareDefaults']['$fPurchaseUnit']) * ((float)$aBasePrice['ShopwareDefaults']['$fReferenceUnit']);
            $sBasePrice = str_replace('&euro;', '€', MLHelper::gi('model_price')->getPriceByCurrency(($fPrice), null, true));
            return
                round($aBasePrice['ShopwareDefaults']['$fPurchaseUnit'], 2).' '
                .$aBasePrice['ShopwareDefaults'][$blLong ? '$sUnitName' : '$sUnit']
                .' ('.$sBasePrice.' */ '.round($aBasePrice['ShopwareDefaults']['$fReferenceUnit'], 2).' '.$aBasePrice['ShopwareDefaults'][$blLong ? '$sUnitName' : '$sUnit'].')';
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBasePrice() {
        $oDetail = $this->getCorrespondingProductEntity();
        $oMaster = $this->getMasterProductEntity();
        if (!is_object($oDetail)) {
            return array();
        }
        $sUnitId = $oDetail->getUnitId();
        if ($sUnitId === null && $oMaster->getUnitId() !== null) {
            $sUnitId = $oMaster->getUnitId();
        }
        if ($sUnitId !== null) {
            $BasePriceCriteria = new Criteria();
            $BasePrice = MagnalisterController::getShopwareMyContainer()->get('unit.repository')->search($BasePriceCriteria->addFilter(new EqualsFilter('id', $sUnitId)), $this->getShopwareContext())->first();

            if (is_object($BasePrice) && $BasePrice->getName() == null) {
                $BasePriceCriteriaReplace = new Criteria();
                $BasePrice = MagnalisterController::getShopwareMyContainer()->get('unit.repository')->search($BasePriceCriteriaReplace->addFilter(new EqualsFilter('id', $sUnitId)), Context::createDefaultContext())->first();
            }

            // If no base price unit was found
            if (!is_object($BasePrice)) {
                return array();
            }

            try {
                $fReferenceUnit = $oDetail->getReferenceUnit();
                if ($fReferenceUnit === null && $oMaster->getReferenceUnit() !== null) {
                    $fReferenceUnit = $oMaster->getReferenceUnit();
                }
                $fPurchaseUnit = $oDetail->getPurchaseUnit();
                if ($fPurchaseUnit === null && $oMaster->getPurchaseUnit() !== null) {
                    $fPurchaseUnit = $oMaster->getPurchaseUnit();
                }
                $sUnitName = $BasePrice->getName();
                $sUnit = $BasePrice->getShortCode();
            } catch (Exception $oEx) {
                return array();
            }
        } else {
            return array();
        }
        if (empty($fReferenceUnit) && empty($fPurchaseUnit)) {//not configured base-price
            return array();
        }
        $fReferenceUnit = $fReferenceUnit <= 0 ? 1 : $fReferenceUnit;
        $fPurchaseUnit = $fPurchaseUnit <= 0 ? 1 : $fPurchaseUnit;
        return array(
            'ShopwareDefaults' => array(
                '$sUnit'          => $sUnit,
                '$sUnitName'      => $sUnitName,
                '$fReferenceUnit' => $fReferenceUnit,
                '$fPurchaseUnit'  => $fPurchaseUnit,
            ),
            'Unit'             => ((string)((float)$fReferenceUnit)).' '.$sUnitName,
            'UnitShort'        => ((string)((float)$fReferenceUnit)).' '.$sUnit,
            'Value'            => ((string)((float)$fPurchaseUnit / $fReferenceUnit)),
        );
    }

    /**
     * return html list, that contain property name and values
     * @return string
     */
    public function getProperties($sSeparator = null) {
        try {
            $sPropertiesHtml = ' ';
            if ($this->getCorrespondingProductEntity()->getPropertyIds() !== null) {
                $aProperties = $this->getPropertiesGrouped();

                $sPropertiesHtml .= '<ul class="magna_properties_list">';
                $sRowClass = 'odd';
                foreach ($aProperties as $sPropertyGroup => $aPropertyValues) {
                    natsort($aPropertyValues);
                    $sPropertiesHtml .= '<li class="magna_property_item '.$sRowClass.'">'
                        .'<span class="magna_property_name">'.$sPropertyGroup
                        .'</span>'
                        .($sSeparator !== null ? $sSeparator : '')
                        .'<span  class="magna_property_value">'.implode(', ', $aPropertyValues)
                        .'</span>'
                        .'</li>';
                    $sRowClass = $sRowClass === 'odd' ? 'even' : 'odd';
                }
                $sPropertiesHtml .= '</ul>';
            }

            return $sPropertiesHtml;
        } catch (Exception $oEx) {
            return '';
        }
    }

    private function getCustomFieldData($sTechnicalName) {
        $iLangId = $this->getLanguage();
        $oCustomFieldSetEnteties = MagnalisterController::getShopwareMyContainer()->get('custom_field.repository');

       return $oCustomFieldSetEnteties->search((new Criteria())->addFilter(new EqualsFilter('name', $sTechnicalName)),
           MLShopware6Alias::getContextByLanguageId($iLangId))
           ->first();
    }

    /**
     *
     * @return array of freetextfield in shopware
     */
    public function getCustomField(): array {
        $aAttributes = [];
        $LangCode = MLFormHelper::getShopInstance()->getLanguageCode();

        $entities = [];

        try {
            $product = $this->getCorrespondingProductEntity();
            $entities[] = $product;


            if ($product !== null && $product->getManufacturer() !== null) {
                $entities[] = $product->getManufacturer();

            }
        } catch (\Throwable $e) {
            // optional: logge Fehler
        }

        try {
            $productFallback = $this->getCorrespondingProductEntityWithDefault();
            $entities[] = $productFallback;
            if ($productFallback !== null && $productFallback->getManufacturer() !== null) {
                $entities[] = $productFallback->getManufacturer();

            }
        } catch (\Throwable $e) {
            // optional: logge Fehler
        }

        foreach ($entities as $entity) {
            $aAttributes += $this->extractCustomFieldsFromEntity($entity, $LangCode);
        }

        return $aAttributes;
    }

    private function extractCustomFieldsFromEntity($entity, string $langCode): array {
        $result = [];

        if (!method_exists($entity, 'getCustomFields')) {
            return $result;
        }

        $customFields = $entity->getCustomFields() ?? [];

        if (!is_array($customFields) && !is_object($customFields)) {
            return $result;
        }

        foreach ($customFields as $key => $value) {
            $fieldData = $this->getCustomFieldData($key);
            if ($fieldData === null || empty($fieldData->getConfig())) {
                continue;
            }

            $config = $fieldData->getConfig();

            if(is_array($config['label'])){
                $label = $config['label'][$langCode] ?? array_values($config['label'])[0] ?? '';
            } elseif(!is_array($config['label']) && !empty($config['label'])){//Other plugins adds global customer field without custom field normal structure and they add it as string
                $label = $config['label'];
            }else{
                $label = '';
            }
            $position = $config['customFieldPosition'] ?? null;

            if (!empty($config['options'])) {
                $value = $this->getCustomfieldsOptionsValueTranslation($config['options'], $value);
            }

            $result[$fieldData->getName()] = [
                'label' => $label,
                'customFieldPosition' => $position,
                'value' => $value,
                'technicalname' => $fieldData->getName(),
            ];
        }

        return $result;
    }


    /**
     * @param $OptionsCustomeFildLabel
     * @return mixed
     */
    public function getCustomfieldsOptionsValueTranslation($OptionsCustomeFildLabel,$value) {
        $LangCode = MLFormHelper::getShopInstance()->getLanguageCode();
        foreach ($OptionsCustomeFildLabel as $value2) {
            if ($value2['value'] == $value) {
                foreach ($value2['label'] as $key3 => $value3) {
                    if ($key3 == $LangCode) {
                        $value = $value3;
                    } elseif (!isset($value2['label'][$LangCode]))  {
                        $value = $value3;
                    }
                }
            }
        }
        return $value;
    }

    protected function getLanguage() {
        $aConfig = MLModule::gi()->getConfig();
        if (isset($aConfig['lang']) && $aConfig['lang'] != NULL && Uuid::isValid($aConfig['lang'])) {
            $iLangId = $aConfig['lang'];
        } else {
            $iLangId = Defaults::LANGUAGE_SYSTEM;
        }
        return $iLangId;
    }

    /**
     * @return array
     */
    public function getReplaceProperty() {
            $aReplaceProperty = parent::getReplaceProperty();
            foreach ($this->getCustomField() as $iPosition => $aAttrValue) {
                $mValue = $aAttrValue['value'];
                if (is_array($mValue)) {
                    $mValue = implode(', ', $mValue);
                }
                // We do not support PriceField as custom field typ
                if (is_object($mValue)) {
                    continue;
                }
                $aReplaceProperty['#Freetextfield' . $aAttrValue['customFieldPosition'] . '#'] = $aReplaceProperty['#Freitextfeld' . $aAttrValue['customFieldPosition'] . '#'] =
                $aReplaceProperty['#Customfield' . $aAttrValue['customFieldPosition'] . '#'] = $aReplaceProperty['#Zusatzfeld' . $aAttrValue['customFieldPosition'] . '#'] =
                $aReplaceProperty['#VALUE_' . $aAttrValue['technicalname'] . '#'] = $mValue;
                $aReplaceProperty['#Description' . $aAttrValue['customFieldPosition'] . '#'] = $aReplaceProperty['#Bezeichnung' . $aAttrValue['customFieldPosition'] . '#'] =
                $aReplaceProperty['#LABEL_' . $aAttrValue['technicalname'] . '#'] = $aAttrValue['label'];
            }

        $aReplaceProperty['#PROPERTIES#'] = $this->getProperties(' : ');
        return $aReplaceProperty;
    }

    public function getReplacePropertyKeys() {
        return array(
            'Freetextfield',
            'Freitextfeld',
            'Description',
            'Bezeichnung',
            'Customfield',
            'Zusatzfeld',
        );
    }

    /**
     * @return int|null
     */
    public function getVariantCount() {
        if ($this->iVariantCount === null) {
            $this->load();
            $iVariantCount = $this->getRealVariantCount();
            $this->iVariantCount = ($iVariantCount == 0) ? 1 : $iVariantCount;
        }
        return $this->iVariantCount;
    }

    /**
     * @return int|null
     */
    public function getRealVariantCount() {
        $mMasterProduct = $this->getMasterProductEntity();
        if (empty($mMasterProduct)) {
            $iVariantCount = 0;
        } else {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('product.parentId', $mMasterProduct->getId()));
            $iVariantCount = MLShopware6Alias::getRepository('product')->search($criteria, $this->getShopwareContext())->count();
        }
        return $iVariantCount;
    }

    /**
     * change current shop, so we can get product information in different languages
     * @param int $iLang
     * @return \ML_Shopware6_Model_Product
     */
    public function setLang($iLang) {
        $this->getShopwareContext();
        $this->oShopwareContext = MLShopware6Alias::getContext($iLang, $this->oShopwareContext->getCurrencyId(), $this->oShopwareContext->getRuleIds());
        return $this;
    }

    /**
     * @param string $mAttributeCode
     * @return float|null
     * @throws Exception
     */
    public function getAttributeValue($mAttributeCode) {
        //MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array('mAttributeCode' => $mAttributeCode));
        return $this->getProductField($mAttributeCode);
    }

    /**
     * @return \Doctrine\ORM\PersistentCollection|int|string|null
     */
    public function getManufacturer() {
        return $this->getModulField('manufacturer');
    }

    /**
     * @return \Doctrine\ORM\PersistentCollection|int|string|null
     */
    public function getManufacturerPartNumber() {
        return $this->getModulField('manufacturerpartnumber');
    }

    /**
     * @return int|string|null
     */
    public function getEAN() {
        return $this->getModulField('ean');
    }

    private $isSingle;
    /**
     * @return bool
     */
    public function isSingle() {
        if($this->isSingle === null) {
            $this->load();
            $oProduct = $this->getCorrespondingProductEntity();
            if ($oProduct->getParentId() !== null) {
                $oProduct = $this->getShopwareProduct($oProduct->getParentId());
            }
            $this->isSingle = (int)$oProduct->getChildCount() === 0;
        }
        return $this->isSingle;
    }

    /**
     * @return Context
     */
    public function getShopwareContext() {
        if ($this->oShopwareContext === null) {
            $this->prepareProductForMarketPlace();
        }
        if ($this->oShopwareContext === null) {//If no marketplace is loaded
            $this->oShopwareContext = Context::createDefaultContext();
        }
        return $this->oShopwareContext;

    }

    /**
     * @param string $sName
     * @return string|null
     */
    public function get($sName) {
        $mValue = parent::get($sName);
        if (
            strtolower($sName) === 'marketplaceidentsku'
            && !empty($mValue)
            && parent::get('ParentID') === '0'
            && MLModule::gi()->getConfig('shopware6.master.sku.migration.options') === '1'
        ) {
            $iLastIndex = strlen((string)$mValue) - 1;
            if ($iLastIndex > 0 && $mValue[$iLastIndex] === 'M' && !$this->isSingle()) {
                // add _Master for migrated customer from shopware 5 when option enabled
                return substr($mValue, 0, $iLastIndex).'_Master';
            }
        }
        return $mValue;

    }

    /**
     * @param ProductEntity|null $oProductEntity
     * @param array $advancedPrice
     * @param CurrencyEntity $oCurrency
     * @return array
     */
    protected function getAdvancedPrice(?ProductEntity $oProductEntity, array $advancedPrice, CurrencyEntity $oCurrency): array {
        try {
            $CriteriaProductPrice = new Criteria();
            $oQuantityFilter = new RangeFilter(
                'quantityStart',
                [
                    RangeFilter::LTE => 1,
                ]
            );
            $oRuleFilter = new EqualsFilter('ruleId', $this->sPriceRules);
            $CriteriaProductPrice->addFilter(new EqualsFilter('productId', $oProductEntity->getId()));
            $CriteriaProductPrice->addFilter($oQuantityFilter);
            $CriteriaProductPrice->addFilter($oRuleFilter);
            $ProductPrice = MLShopware6Alias::getRepository('product_price')->search($CriteriaProductPrice, $this->getShopwareContext())->first();
            if ($ProductPrice === null && $oProductEntity->getParentId() !== null) {
                $ParentProductAdvancedPrice = $this->getShopwareProduct($oProductEntity->getParentId());
                $CriteriaProductPrice = new Criteria();
                $CriteriaProductPrice->addFilter(new EqualsFilter('productId', $ParentProductAdvancedPrice->getId()));
                $CriteriaProductPrice->addFilter($oQuantityFilter);
                $CriteriaProductPrice->addFilter($oRuleFilter);
                $ProductPrice = MLShopware6Alias::getRepository('product_price')->search($CriteriaProductPrice, $this->getShopwareContext())->first();
            }
            /** @var $ProductPrice Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity */
            if ($ProductPrice != null) {
                $oCurrencyPrice = $ProductPrice->getPrice()->getCurrencyPrice($this->getShopwareContext()->getCurrencyId());
                if (
                    MLShopware6Alias::getCurrencyModel()->isDefaultCurrency($oCurrencyPrice->getCurrencyId()) &&
                    $oCurrencyPrice->getCurrencyId() !== $oCurrency->getId()
                ) {
                    $advancedPrice = ['net' => $oCurrencyPrice->getNet() * $oCurrency->getFactor(), 'gross' => $oCurrencyPrice->getGross() * $oCurrency->getFactor()];
                } else {
                    $advancedPrice = ['net' => $oCurrencyPrice->getNet(), 'gross' => $oCurrencyPrice->getGross()];
                }
            }
        } catch (\Throwable $ex) {
            MLMessage::gi()->addDebug($ex);
            return [];
        }

        return $advancedPrice;
    }

    /**
     * @param ProductEntity|null $oProductEntity
     * @param array $advancedPrice
     * @return array
     */
    public function getVolumePrices($sGroup, $blGross = true, $sPriceKind = '', $fPriceFactor = 0.0, $iPriceSignal = null) {
        $oProductEntity = $this->getCorrespondingProductEntity();
        $aVolumePrices = [];
        $oQuantitySorting = new FieldSorting('quantityStart', FieldSorting::ASCENDING);
        $oRuleFilter = new EqualsFilter('ruleId', $sGroup);
        $CriteriaProductPrice = new Criteria();
        $CriteriaProductPrice->addFilter(new EqualsFilter('productId', $oProductEntity->getId()));
        $CriteriaProductPrice->addFilter($oRuleFilter);
        $CriteriaProductPrice->addSorting($oQuantitySorting);
        $ProductPrices = MLShopware6Alias::getRepository('product_price')
            ->search($CriteriaProductPrice, $this->getShopwareContext())->getEntities();
        if ($ProductPrices->count() === 0 && $oProductEntity->getParentId() !== null) {
            $ParentProductAdvancedPrice = $this->getShopwareProduct($oProductEntity->getParentId());
            $CriteriaProductPrice = new Criteria();
            $CriteriaProductPrice->addFilter(new EqualsFilter('productId', $ParentProductAdvancedPrice->getId()));
            $CriteriaProductPrice->addFilter($oRuleFilter);
            $CriteriaProductPrice->addSorting($oQuantitySorting);
            $ProductPrices = MLShopware6Alias::getRepository('product_price')
                ->search($CriteriaProductPrice, $this->getShopwareContext())->getEntities();
        }

        if ($ProductPrices->count() > 0) {
            $oPrice = MLPrice::factory();

            foreach ($ProductPrices as $ProductPrice) {
                if ($ProductPrice->getQuantityStart() > 1) {// it is normal price, so ignore 1
                    foreach ($ProductPrice->getPrice() as $value) {
                        if ($value->getCurrencyId() == $this->getShopwareContext()->getCurrencyId()) {

                            $fGrossPrice = $value->getGross();
                            $fNetPrice = $value->getNet();

                            // Marketplace Configuration - Addition or percentage price change
                            if ($sPriceKind === 'percent') {
                                $fGrossPrice = $oPrice->calcPercentages(null, $fGrossPrice, $fPriceFactor);
                                $fNetPrice = $oPrice->calcPercentages(null, $fNetPrice, $fPriceFactor);
                            } elseif ($sPriceKind === 'addition') {
                                $fGrossPrice = $fGrossPrice + $fPriceFactor;
                                $fNetPrice = $fNetPrice + $fPriceFactor;
                            }

                            // Marketplace Configuration - Price Signal
                            if ($iPriceSignal !== null) {
                                //If price signal is single digit then just add price signal as last digit
                                if (strlen((string)$iPriceSignal) == 1) {
                                    $fGrossPrice = (0.1 * (int)($fGrossPrice * 10)) + ((int)$iPriceSignal / 100);
                                    $fNetPrice = (0.1 * (int)($fNetPrice * 10)) + ((int)$iPriceSignal / 100);
                                } else {
                                    $fGrossPrice = ((int)$fGrossPrice) + ((int)$iPriceSignal / 100);
                                    $fNetPrice = ((int)$fNetPrice) + ((int)$iPriceSignal / 100);
                                }
                            }

                            $fPrice = round($blGross ? $fGrossPrice : $fNetPrice, 2);
                            $aVolumePrices[$ProductPrice->getQuantityStart()] = $this->priceAdjustment($fPrice);
                            break; // run only once for a specific currency
                        }
                    }
                }
            }
        }
        return $aVolumePrices;
    }

    /**
     * @param $iProductID
     * @param null $oContext
     * @param array $aAssociations
     * @return ProductEntity
     */
    protected function getShopwareProduct($iProductID, $oContext = null, array $aAssociations = []) {
        $oContext = $oContext !== null ? $oContext : $this->getShopwareContext();
        $oShopProductIDCriteria = MLHelper::gi("model_product")->initiateCriteriaObjectWithMedia();
        $aAssociations[]='manufacturer';
        $oShopProductIDCriteria->addAssociations($aAssociations);
        $oShopProduct = MLShopware6Alias::getRepository('product')
            ->search($oShopProductIDCriteria->addFilter(new EqualsFilter('product.id', $iProductID)), $oContext)->first();
        return $oShopProduct;
    }

    /**
     * @param ProductEntity|null $oProductEntity
     * @param $defaultSalesChannel
     * @param array $advancedPrice
     * @param CurrencyEntity $CurrencyObject
     * @return float[]|int[]
     */
    protected function getShopware63Price(?ProductEntity $oProductEntity, array $advancedPrice, CurrencyEntity $CurrencyObject): array {
        $context = MagnalisterController::getSalesChannelContextFactory()
            ->create($oProductEntity->getId(), $this->getSalesChannel(), [SalesChannelContextService::CURRENCY_ID => $this->getShopwareContext()->getCurrencyId()]);
        if (!isset($advancedPrice['net'])) {
            $fBrutPrice = MagnalisterController::getProductPriceDefinitionBuilder()->build($oProductEntity, $context)->getPrice()->getPrice();
        } else {
            $fBrutPrice = $advancedPrice['gross'] * $CurrencyObject->getFactor();
        }
        $context->setTaxState(CartPrice::TAX_STATE_NET);
        if (!isset($advancedPrice['net'])) {
            $fNetPrice = MagnalisterController::getProductPriceDefinitionBuilder()->build($oProductEntity, $context)->getPrice()->getPrice();
        } else {
            $fNetPrice = $advancedPrice['net'] * $CurrencyObject->getFactor();
        }
        return array($fBrutPrice, $fNetPrice);
    }

    /**
     * @param ProductEntity|null $oProductEntity
     * @param array $advancedPrice
     * @param CurrencyEntity $oCurrency
     * @return array
     */
    protected function getShopware64Price(?ProductEntity $oProductEntity, array $advancedPrice, CurrencyEntity $oCurrency): array {
        if (!isset($advancedPrice['net'])) {
            $oCurrencyPrice = $oProductEntity->getPrice()->getCurrencyPrice($oCurrency->getId());
            if (
                MLShopware6Alias::getCurrencyModel()->isDefaultCurrency($oCurrencyPrice->getCurrencyId()) &&
                $oCurrencyPrice->getCurrencyId() !== $oCurrency->getId()
            ) {
                $fBrutPrice = $oCurrencyPrice->getGross() * $oCurrency->getFactor();
                $fNetPrice = $oCurrencyPrice->getNet() * $oCurrency->getFactor();
            } else {
                $fBrutPrice = $oCurrencyPrice->getGross();
                $fNetPrice = $oCurrencyPrice->getNet();
            }
        } else {
            $fBrutPrice = $advancedPrice['gross'];
            $fNetPrice = $advancedPrice['net'];
        }
        return array($fBrutPrice, $fNetPrice);
    }

    public function getPropertiesGrouped(): array {
        $aProperties = array();
        foreach ($this->getCorrespondingProductEntity()->getPropertyIds() as $value) {
            $OptionPropertiesEntitiesCriteria = new Criteria();
            $OptionPropertiesEntites = MagnalisterController::getShopwareMyContainer()->get('property_group_option.repository')->search($OptionPropertiesEntitiesCriteria->addFilter(new EqualsFilter('id', $value)), $this->getShopwareContext())->first();
            if (null === $OptionPropertiesEntites) {
                continue;
            }

            $groupentity = MagnalisterController::getShopwareMyContainer()->get('property_group.repository');
            $group = $groupentity->search(new Criteria(['id' => $OptionPropertiesEntites->getGroupId()]), $this->getShopwareContext())->first();
            if ($OptionPropertiesEntites->getName() === NULL) {
                $DefaultLangCriteria2 = new Criteria();
                $OptionPropertiesEntites = MagnalisterController::getShopwareMyContainer()->get('property_group_option.repository')->search($DefaultLangCriteria2->addFilter(new EqualsFilter('id', $value)), Context::createDefaultContext())->first();
                $DefaultGroupentity = MagnalisterController::getShopwareMyContainer()->get('property_group.repository');
                if ($group->getName() == null) {
                    $group = $DefaultGroupentity->search(new Criteria(['id' => $OptionPropertiesEntites->getGroupId()]), Context::createDefaultContext())->first();
                } else {
                    $group = $DefaultGroupentity->search(new Criteria(['id' => $OptionPropertiesEntites->getGroupId()]), $this->getShopwareContext())->first();
                }
            }
            if($group !==null){
                //somehow, some of the properties in shopware 6 has been stored with removed or noun exist property groups
                $aProperties[$group->getName()][] = $OptionPropertiesEntites->getName();
            }

        }
        return $aProperties;
    }

    /**
     * @param $mValue
     * @return mixed
     */
    protected function getProductUnitValueAttributeMatching($mValue) {
        $criteria = new Criteria();
        $mValue1 = MagnalisterController::getShopwareMyContainer()->get('unit.repository')->search($criteria->addFilter(new EqualsFilter('id', $mValue)), $this->getShopwareContext())->first();
        if ($mValue1 !== null) {
            if ($mValue1->getName() !== null) {
                $mValue = $mValue1->getName();
            } else {
                $criteriaReplaceTranslation = new Criteria();
                $mValue2 = MagnalisterController::getShopwareMyContainer()->get('unit.repository')->search($criteriaReplaceTranslation->addFilter(new EqualsFilter('id', $mValue)), Context::createDefaultContext())->first();
                $mValue = $mValue2->getName();
            }
        }
        return $mValue;
    }

    /**
     * @param $mValue
     * @return mixed
     */
    protected function getProductManufactureValueAttributeMatching($mValue) {
        $criteria = new Criteria();
        $mValue1 = MagnalisterController::getShopwareMyContainer()->get('product_manufacturer.repository')->search($criteria->addFilter(new EqualsFilter('id', $mValue)), $this->getShopwareContext())->first();
        if ($mValue1 !== null) {
            if ($mValue1->getName() !== null) {
                $mValue = $mValue1->getName();
            } else {
                $criteriaReplaceTranslation = new Criteria();
                $mValue2 = MagnalisterController::getShopwareMyContainer()->get('product_manufacturer.repository')->search($criteriaReplaceTranslation->addFilter(new EqualsFilter('id', $mValue)), Context::createDefaultContext())->first();
                $mValue = $mValue2->getName();
            }
        }
        return $mValue;
    }

    protected function getCurrencyWithISO($currency1) {
        $currency = MLShopware6Alias::getRepository('currency')
            ->search((new Criteria())->addFilter(new EqualsFilter('isoCode', (string)$currency1)), Context::createDefaultContext())->first();
        if ($currency == null) {
            throw new Exception('Currency ' . $currency1 . ' doesn\'t exist in your shop ');
        }
        return $currency;
    }

    protected function getCorrespondingProductEntityWithDefault() {
        if ($this->oCorrespondingProductEntityWithDefault === null) {
            $this->oCorrespondingProductEntityWithDefault = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getId(), Context::createDefaultContext());
        }
        return $this->oCorrespondingProductEntityWithDefault;
    }

    protected function getMasterProductEntityWithDefault() {
        if ($this->oMasterProductEntityWithDefault === null) {
            if ($this->getCorrespondingProductEntity()->getParentId() !== null) {
                $this->oMasterProductEntityWithDefault = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId(), Context::createDefaultContext());
            } else {
                $this->oMasterProductEntityWithDefault = $this->getCorrespondingProductEntityWithDefault();
            }
        }
        return $this->oMasterProductEntityWithDefault;
    }

    protected function getVariantNamePostfix($optionsentity, $attribute) {
        // Get all option IDs
        $optionIds = $this->getCorrespondingProductEntity()->getOptionIds();
        if (empty($optionIds)) {
            return $attribute;
        }

        // Collect all option entities
        $optionsCollection = [];
        foreach ($optionIds as $oOption) {
            $optionEntity = $optionsentity->search(new Criteria(['id' => $oOption]), $this->getShopwareContext())->first();
            // Product has options and an attribute but the option themselves doesn't exist
            if (!is_object($optionEntity)) {
                continue;
            }
            $optionsCollection[] = $optionEntity;
        }

        // Sort options by group ID
        usort($optionsCollection, function ($a, $b) {
            return $a->getGroupId() <=> $b->getGroupId();
        });

        // Process sorted options
        foreach ($optionsCollection as $optionsName) {
            if ($optionsName->getName() !== null) {
                $mPropertyValue = $optionsName->getName();
            } else {
                $optionsNameReplace = $optionsentity->search(new Criteria(['id' => $optionsName->getId()]), Context::createDefaultContext())->first();
                $mPropertyValue = $optionsNameReplace->getName();
            }

            $attribute .= ' : ' . $mPropertyValue;
        }
        return $attribute;
    }

    /**
     * This function go through all possible cases where with considering of priority and get the value
     * 1. check the current product with marketplace configured language
     * 2. check the current product with default language/context
     * 3. check the parent product (if it is variant-product) with marketplace configured language
     * 4. check the parent product (if it is variant-product) with default language/context
     *
     * @param string $fieldName
     * @return mixed
     * @throws Exception
     */
    protected function getProductFieldValue(string $fieldName) {
        $method = 'get' .$fieldName;
        if (!method_exists($this->getCorrespondingProductEntity(), $method)) {
            throw new \Exception('Method ' . $method . ' doesn\'t exist in your shop');
        }
        if($this->getMasterProductEntity()->$method()!== null) {
            $parentValue = $this->getMasterProductEntity()->$method();
        }else{
            $parentValue = $this->getMasterProductEntityWithDefault()->$method();
        }
        if ($this->isSingle() || $this->getCorrespondingProductEntity()->$method() === null) {
            $mValue = $parentValue;
        } else {
            $mValue = $this->getCorrespondingProductEntity()->$method();
        }

        return $mValue;
    }

    /**
     * Gets the variation attribute value for a given attribute name
     * @param string $sName The attribute name without the 'a_' prefix
     * @param bool $blMultiValue Whether to return multiple values concatenated
     * @return string The attribute value(s)
     */
    protected function getProductFieldVariationAttribute($sName, $blMultiValue = false) {
        $mValue = '';
        if ($this->getCorrespondingProductEntity()->getOptionIds() !== null) {
            foreach ($this->getCorrespondingProductEntity()->getOptionIds() as $value) {
                $OptionEntitiesCriteria = new Criteria();
                $OptionEntities = MagnalisterController::getShopwareMyContainer()
                    ->get('property_group_option.repository')
                    ->search($OptionEntitiesCriteria->addFilter(new EqualsFilter('id', $value)), $this->getShopwareContext())
                    ->first();
                
                if (!is_object($OptionEntities)) {
                    continue;
                }

                if ($OptionEntities->getGroupId() == $sName) {
                    if ($OptionEntities->getName() !== NULL) {
                        $mPropertyValue = $OptionEntities->getName();
                    } else {
                        $DefaultLangCriteria = new Criteria();
                        $DefaultLangOptionEntities = MagnalisterController::getShopwareMyContainer()
                            ->get('property_group_option.repository')
                            ->search($DefaultLangCriteria->addFilter(new EqualsFilter('id', $value)), Context::createDefaultContext())
                            ->first();
                        $mPropertyValue = $DefaultLangOptionEntities->getName();
                    }

                    if ($blMultiValue) {
                        $mValue .= (($mValue === '') ? '' : ',') . $mPropertyValue;
                    } else {
                        $mValue = $mPropertyValue;
                        break;
                    }
                }
            }
        }
        return $mValue;
    }

    /**
     * Gets the property value for a given property name
     * @param string $sName The property name without the 'p_' prefix
     * @param bool $blMultiValue Whether to return multiple values concatenated
     * @return string The property value(s)
     */
    protected function getProductFieldProperties($sName, $blMultiValue = false) {
        $mValue = '';
        $propertyIds = $this->getCorrespondingProductEntity()->getPropertyIds();

        if ($propertyIds === null || empty($propertyIds)) {
            return $mValue;
        }

        // Batch load all property options at once for better performance
        $batchCriteria = new Criteria($propertyIds);
        $batchCriteria->addAssociation('group');
        $propertyOptions = MagnalisterController::getShopwareMyContainer()
            ->get('property_group_option.repository')
            ->search($batchCriteria, $this->getShopwareContext());

        // If some properties are missing, try loading them with default context
        if ($propertyOptions->count() < count($propertyIds)) {
            $foundIds = array_map(function($option) { return $option->getId(); }, $propertyOptions->getElements());
            $missingIds = array_diff($propertyIds, $foundIds);

            if (!empty($missingIds)) {
                $fallbackCriteria = new Criteria($missingIds);
                $fallbackCriteria->addAssociation('group');
                $fallbackOptions = MagnalisterController::getShopwareMyContainer()
                    ->get('property_group_option.repository')
                    ->search($fallbackCriteria, Context::createDefaultContext());

                // Merge fallback options with existing ones
                foreach ($fallbackOptions->getElements() as $option) {
                    $propertyOptions->add($option);
                }
            }
        }

        // Process each property option
        foreach ($propertyIds as $propertyId) {
            $optionEntity = $propertyOptions->get($propertyId);

            if ($optionEntity === null) {
                continue;
            }

            // Check if this property belongs to the requested group
            if ($optionEntity->getGroupId() !== $sName) {
                continue;
            }

            // Get the property value with language fallback
            $propertyValue = $optionEntity->getName();
            if ($propertyValue === null) {
                // Try to get name from default context
                $defaultCriteria = new Criteria([$propertyId]);
                $defaultOption = MagnalisterController::getShopwareMyContainer()
                    ->get('property_group_option.repository')
                    ->search($defaultCriteria, Context::createDefaultContext())
                    ->first();
                $propertyValue = $defaultOption !== null ? $defaultOption->getName() : '';
            }

            if ($blMultiValue) {
                // Adding ',,' separator character to prevent conflicts with values that contain ','
                $mValue .= (($mValue === '') ? '' : ',,') . $propertyValue;
            } else {
                $mValue = $propertyValue;
                break;
            }
        }

        return $mValue;
    }

    protected function getProductFieldModelField($sName, $mValue) {
        try {
            $mValue = $this->getCorrespondingProductEntity()->{'get' . $sName}();
        } catch (\Throwable $ex) {
            MLMessage::gi()->addDebug($ex);
        }
        if ($mValue === null) {
            //if it is variant and value is inherited
            if ($this->getCorrespondingProductEntity()->getParentId() !== null) {
                $oMasterProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId());
                $mValue = $oMasterProduct->{'get' . $sName}();
                //if it is variant product and value is inherited from default language
                if ($mValue == null) {
                    $oMasterProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getParentId(), Context::createDefaultContext());
                    $mValue = $oMasterProduct->{'get' . $sName}();
                }
            } else {//if it is Master product and value is inherited from default language
                $oMasterProduct = $this->getShopwareProduct($this->getCorrespondingProductEntity()->getId(), Context::createDefaultContext());
                $mValue = $oMasterProduct->{'get' . $sName}();
            }
        }
        if ($sName == 'ManufacturerId') {
            $mValue = $this->getProductManufactureValueAttributeMatching($mValue);
        } elseif ($sName == 'UnitId') {
            $mValue = $this->getProductUnitValueAttributeMatching($mValue);
        }
        return $mValue;
    }

    protected function getProductFieldManufacturerCustomField($sName, string $mValue) {
        $sName = substr($sName, 3);
        $mValue = $this->getProductCustomFieldValue($sName, $mValue,'product_manufacturer');
        $customField = $this->getCustomFieldData($sName);
        if (is_object($customField) && $customField->getType() === 'select' && $mValue !== '') {
            $mValue = $this->getCustomFieldSelectTypeValue($mValue, $customField);
        }
        return $mValue;
    }

    /**
     * Helper method to get list price value from a product entity
     * 
     * @param string $priceType The price type to retrieve (e.g. 'Gross', 'Net')
     * @param ProductEntity|null $productEntity The product entity to extract price from
     * @return mixed The price value or null if not available
     */
    private function getListPriceValue(string $priceType, ?ProductEntity $productEntity)
    {
        if ($productEntity === null) {
            return null;
        }
        
        $price = $productEntity->getPrice();
        if ($price === null) {
            return null;
        }
        
        $currencyPrice = $price->getCurrencyPrice($this->getShopwareContext()->getCurrencyId());
        if ($currencyPrice === null) {
            return null;
        }
        
        $listPrice = $currencyPrice->getListPrice();
        if ($listPrice === null) {
            return null;
        }
        
        return $listPrice->{'get'.$priceType}();
    }

    /**
     * Get product custom field value
     * 
     * @param string $sName Field name without prefix
     * @param bool $blMultiValue Whether to return multiple values
     * @return mixed Custom field value
     */
    protected function getProductFieldCustomField($sName, $blMultiValue = false) {
        $mValue = '';
        $mValue = $this->getProductCustomFieldValue($sName, $mValue, 'product');
        $customField = $this->getCustomFieldData($sName);
        if (is_object($customField) && $customField->getType() === 'select' && $mValue !== '') {
            $mValue = $this->getCustomFieldSelectTypeValue($mValue, $customField);
        }
        if (is_object($customField) && isset($customField->getConfig()['customFieldType']) &&
            $customField->getConfig()['customFieldType'] === 'media' && $mValue !== '') {
            $criteria = new Criteria();
            $oMedia = MagnalisterController::getShopwareMyContainer()
                ->get('media.repository')
                ->search(
                    $criteria->addFilter(
                        new EqualsFilter('id', $mValue)
                    ), $this->getShopwareContext()
                )->first();
            $mValue = $oMedia->getUrl();
        }
        return $mValue;
    }

    /**
     * Get list price field value from product
     * 
     * @param string $sName Field name with ListPrice_ prefix
     * @param mixed $mValue Default value
     * @return mixed List price value
     */
    protected function getProductFieldListPrice($sName, $mValue) {
        $priceType = str_replace('ListPrice_', '', $sName);
        $mValue = $this->getListPriceValue($priceType, $this->getCorrespondingProductEntity());
            
        if(empty($mValue)){
            $mValue = $this->getListPriceValue($priceType, $this->getMasterProductEntity());
        }
        
        return $mValue;
    }

}
