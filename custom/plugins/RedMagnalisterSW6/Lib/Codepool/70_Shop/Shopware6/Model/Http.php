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
use Shopware\Storefront\Framework\Csrf\CsrfModes;
use Shopware\Storefront\Framework\Csrf\CsrfPlaceholderHandler;
use Shopware\Storefront\Framework\Twig\Extension\CsrfFunctionExtension;

class ML_Shopware6_Model_Http extends ML_Shop_Model_Http_Abstract {

    protected $oMedia;

    public function setMedia($oMedia) {
        $this->oMedia = $oMedia;
    }

    /**
     * Gets the url to a file in the resources folder.
     * @param string $sFile
     *    Filename
     * @param bool $blAbsolute
     *
     * @return string
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     */
    public function getResourceUrl($sFile = '', $blAbsolute = true) {
        if ($sFile == '') {
            if ($blAbsolute) {
                $returnValue = str_replace('public/index.php', 'public/css', MagnalisterController::getShopwareRequest()->server->get('SCRIPT_FILENAME'));
                return str_replace('public_html/index.php', 'public_html/css', $returnValue);
            } else {
                return '';
            }
        }

        $sBaseURL = MagnalisterController::getShopwareRequest()->getHttpHost().'/'.MagnalisterController::getShopwareRequest()->getBasePath();
        $sUrl = MagnalisterController::getShopwareRequest()->getScheme().'://'.str_replace('//', '/', $sBaseURL);
        $aResource = MLFilesystem::gi()->findResource('resource_'.$sFile);
        $sRelLibPath = substr($aResource['path'], strlen(MLFilesystem::getLibPath().'Codepool'));
        $sResourceType = strtolower(preg_replace('/^.*\/resource\/(.*)\/.*$/Uis', '$1', $sRelLibPath));
        if (basename($sResourceType) === 'js') {
            $mediaPath = MagnalisterController::getShopwareMyContainer()->get('kernel')->getProjectDir().'/public/js';
            $sDstPath = $mediaPath.'/magnalister'.$sRelLibPath;
        }
        if (basename($sResourceType) === 'css') {
            $mediaPath = MagnalisterController::getShopwareMyContainer()->get('kernel')->getProjectDir().'/public/css';
            $sDstPath = $mediaPath.'/magnalister'.$sRelLibPath;
        }
        if (basename($sResourceType) === 'images') {
            $mediaPath = MagnalisterController::getShopwareMyContainer()->get('kernel')->getProjectDir().'/public/css';
            $sDstPath = $mediaPath.'/magnalister'.$sRelLibPath;
        }
        if (!file_exists($sDstPath)) {// we copy complete resource-type-folder if one file not exists
            $sSubPath = preg_replace('/^(.*\/resource\/.*)\/.*$/Uis', '$1', $sRelLibPath);
            $sSrcPath = substr($aResource['path'], 0, stripos($aResource['path'], $sSubPath) + strlen($sSubPath) + 1);
            $sDstPath = substr($sDstPath, 0, stripos($sDstPath, $sSubPath) + strlen($sSubPath) + 1);
            try {
                MLHelper::getFilesystemInstance()->cp($sSrcPath, $sDstPath);
            } catch (Exception $oEx) {
                MLMessage::gi()->addDebug($oEx, array(
                    '$sSrcPath' => $sSrcPath,
                    '$sDstPath' => $sDstPath,
                    '$sSubPath' => $sSubPath
                ));
                MLMessage::gi()->addError(MLI18n::gi()->get('sMessageCannotLoadResource'));
                MLSetting::gi()->set('blInlineResource', true, true);
            }
        }

        if ($sResourceType === 'js') {
            $sUrl .= '/js/magnalister'.$sRelLibPath;
        } elseif ($sResourceType === 'css') {
            $sUrl .= '/css/magnalister'.$sRelLibPath;
        } else {
            $sUrl .= '/css/magnalister'.$sRelLibPath;
        }

        $sUrl = $this->normalizeUrl($sUrl);

        return str_replace('\\', '/', $sUrl);
    }

    protected function normalizeUrl($url) {
        // Replace double slashes with single slashes, but keep http:// or https:// intact
        return preg_replace('#(?<!:)//+#', '/', $url);
    }

    /**
     * just used for cUrl referer for api request .
     * @return string
     */
    public function getBaseUrl() {
        if (MLSetting::gi()->sDebugHost !== null) {
            return MLSetting::gi()->get('sDebugHost');
        } else {
            $sBaseURL = MagnalisterController::getShopwareRequest()->getHttpHost().'/'.MagnalisterController::getShopwareRequest()->getBasePath();
            return MagnalisterController::getShopwareRequest()->getScheme().'://'.str_replace('//', '/', $sBaseURL);
        }
    }


    /**
     * Get the real path in sDebugHost is filled in dev.php
     * @return string
     */
    protected function getRealPath(): string {
        $sBaseURL = MagnalisterController::getShopwareRequest()->getHttpHost() . '/' . MagnalisterController::getShopwareRequest()->getBasePath();
        return MagnalisterController::getShopwareRequest()->getScheme() . '://' . str_replace('//', '/', $sBaseURL);
    }

    /**
     * return url of administration of current shopware shop
     * @return string
     */
    public function getAdminUrl(): string {
        $adminPathName = $_SERVER['SHOPWARE_ADMINISTRATION_PATH_NAME'] ?? '/admin';
        $sBaseURL = MagnalisterController::getShopwareRequest()->getHttpHost().'/'.MagnalisterController::getShopwareRequest()->getBasePath().$adminPathName;
        return MagnalisterController::getShopwareRequest()->getScheme().'://'.str_replace('//', '/', $sBaseURL);
    }

    protected function getMagnalisterURLForShopware6() {
        if (
            (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off') ||
            (!empty($_SERVER["HTTP_X_FORWARDED_SSL"]) && $_SERVER["HTTP_X_FORWARDED_SSL"] !== 'off') ||
            (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === 'https')
        ) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        //MagnalisterController::getShopwareRequest()->getRequestUri() doesn't work properly when a virtual sub directly is used
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . explode("?", $_SERVER['REQUEST_URI'])[0];
    }

    /**
     * Gets the backend url of the magnalister app.
     * @param array $aParams
     *    name => value
     * @return string
     */
    public function getUrl($aParams = array()) {
        $sParent = parent::getUrl($aParams);

        return $this->getMagnalisterURLForShopware6().(($sParent == '') ? '' : '?'.$sParent);
    }

    /**
     * Gets the request params merged from _POST and _GET.
     * @return array
     */
    public function getRequest() {
        $aOut = MLHelper::getArrayInstance()->mergeDistinct(MagnalisterController::getShopwareRequest()->request->all(), MagnalisterController::getShopwareRequest()->query->all());
        return $this->filterRequest($aOut);
    }

    /**
     * Returns _SERVER.
     * @return array
     */
    public function getServerRequest() {
        return $_SERVER;
    }

    /**
     * Parse hidden fields that are wanted by different shop systems for security measurements.
     * @return array
     *    Assoc of hidden necessary form fields array (name => value, ...)
     */
    public function getNeededFormFields() {
        if (version_compare(MLSHOPWAREVERSION, '6.5.0.0', '>=')) {
            return array();
        }else{
            return array(
                '_csrf_token' => self::getCSRFToken()
            );
        }

    }

    static protected $csrfToken;

    private static function getCSRFToken() {
        if (self::$csrfToken === null) {
            if (MagnalisterController::getShopwareMyContainer()->getParameter('storefront.csrf.mode') === CsrfModes::MODE_TWIG) {
                $intent = 'magnalister.admin.page';
            } else {
                $intent = 'ajax';
            }
            /** @var Symfony\Component\Security\Csrf\CsrfTokenManager $sCsrfTokeClass */
            $sCsrfTokeClass = MagnalisterController::getShopwareMyContainer()->get('security.csrf.token_manager');
            self::$csrfToken = $sCsrfTokeClass
                ->getToken($intent)
                ->getValue();
        }
        return self::$csrfToken;
    }

    /**
     * Gets the magnalister cache FS url.
     * @return string
     * @todo Create logic to get file of cache like implementation in Magento
     */
    public function getCacheUrl($sFile = '') {
        $path = MagnalisterController::getShopwareMyContainer()->get('kernel')->getProjectDir() . '/public/RedMagnalister/'.$sFile;

        //remove existing Zip Translation
        if (file_exists($path)) {
            MLHelper::getFilesystemInstance()->rm($path);
        }
        //copy generated translation zip file from cach file to public/RedMagnalister folder
        MLHelper::getFilesystemInstance()->cp(MLFilesystem::getCachePath($sFile), $path);
        return  $this->getRealPath().'/RedMagnalister/'.$sFile;
    }

    /**
     * Gets the frontend url of the magnalister app.
     * @param array $aParams
     * @return string
     */
    public function getFrontendDoUrl($aParams = array()) {
        $sConfig = $this->getConfigFrontCronURL($aParams);
        if ($sConfig !== '') {
            return $sConfig;
        }
        $sParent = parent::getUrl($aParams);
        $sURL = $this->getMagnalisterURLForShopware6();
        $sURL = substr($sURL, 0, strrpos($sURL, '/magnalister/'));
        $return = $sURL.'/magnalister/'.(($sParent == '') ? '' : '?'.$sParent);
        return $return;
    }

    /**
     * return directory or path (file system) of specific shop images
     * @return string
     */
    public function getShopImagePath() {
        return 'media/';
    }

    /**
     * return url of specific shop images
     * @param string $sFiles
     */
    public function getShopImageUrl() {
        $sBaseURL = MagnalisterController::getShopwareRequest()->getHttpHost().'/'.MagnalisterController::getShopwareRequest()->getBasePath().'/media/';
        return MagnalisterController::getShopwareRequest()->getScheme().'://'.str_replace('//', '/', $sBaseURL);
    }

    /**
     * return directory or path (file system) of specific shop images
     * @param string $sFiles
     */
    public function getImagePath($sFile) {
        if(self::$sImagePath === null ){
            $sShopwareImagePath = $this->getShopImagePath();
            if (MagnalisterController::getFileSystem()->has($sShopwareImagePath)) {
                if (!MagnalisterController::getFileSystem()->has($sShopwareImagePath.'magnalister/')) {
                    if (method_exists(MagnalisterController::getFileSystem(), 'createDir')) {//6.4
                        MagnalisterController::getFileSystem()->createDir($sShopwareImagePath . 'magnalister/');
                    } else {//6.5
                        MagnalisterController::getFileSystem()->createDirectory($sShopwareImagePath . 'magnalister/');
                    }
                }
                self::$sImagePath = $sShopwareImagePath.'magnalister/';
            }else{
                MLMessage::gi()->addDebug(MLI18n::gi()->get('sException_update_pathNotWriteable', array(
                    'path' => $sShopwareImagePath
                )));
                throw new Exception('cannot create images');
            }
        }
        return self::$sImagePath.$sFile;
    }

    public function getImageUrl($sFile) {
        $sUrl = MagnalisterController::getMediaConverter()->getAbsoluteMediaUrl(MLImage::gi()->getMediaObject($sFile));
        $sBaseUrl = '';
        if (strpos($sUrl, '/media/') !== false) {
            $sBaseUrl = explode('/media/', $sUrl)[0].'/media/';
        }
        $sMagnalisterImageUrl = $sBaseUrl.'magnalister/'.$sFile;
        return $sMagnalisterImageUrl;
    }


}
