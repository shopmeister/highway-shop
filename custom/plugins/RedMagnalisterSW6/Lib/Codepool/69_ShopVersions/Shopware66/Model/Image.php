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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

use Redgecko\Magnalister\Controller\MagnalisterController;
use Redgecko\Magnalister\Service\MediaConverter;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;

MLFilesystem::gi()->loadClass('Shopware6_Model_Image');

class ML_Shopware66_Model_Image extends ML_Shopware6_Model_Image {






    protected function getShopwareImageUrlWithImageName($sSrc): string {
        $media = $this->getMediaObject($sSrc);
        if ($media === null) {
            throw new \Exception('The image cannot be found in media table of shopware:'.$sSrc);
        }


        $mediaBuilder= MagnalisterController::getMediaLocationBuilder()->media(array($media->getId()));

        $imagePath = MagnalisterController::getMediaPathStrategy()->generate($mediaBuilder);

//        echo print_m($imagePath[$media->getId()]);
//        die('test');

        return $imagePath[$media->getId()];
    }


    public function getDestinationPath($sSrc, $sType, $iMaxWidth, $iMaxHeight) {
        if (MLHelper::getRemote()->isUrl($sSrc)) {
            $sSrc = parse_url($sSrc, PHP_URL_PATH);
        }
        $sFileName = pathinfo($sSrc, PATHINFO_BASENAME);
        $sDst = $sType.'/'.$iMaxWidth.($iMaxWidth === $iMaxHeight ? '' : 'x'.$iMaxHeight).'px/'.$sFileName;
        return $sDst;
    }

}
