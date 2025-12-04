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

abstract class ML_Modul_Model_Service_CacheAPICalls_Abstract extends ML_Modul_Model_Service_Abstract {
    protected $aCachedCalls = array();

    protected $aRequestTimeouts = array();

    protected $iCacheTimeLimit = 28800;

    public function __construct() {
        MLDatabase::getDbInstance()->logQueryTimes(false);
        MagnaConnector::gi()->setTimeOutInSeconds(600);
        $language = MLModule::gi()->getConfig('admin.user.language');
        if($language !== null) {
            MagnaConnector::gi()->setLanguage($language);
        }
        @set_time_limit(60 * 10); // 10 minutes per module
        parent::__construct();
    }

    public function __destruct() {
        MagnaConnector::gi()->resetTimeOut();
        MLDatabase::getDbInstance()->logQueryTimes(true);
    }

    protected function getDataRequest($action, $parameters = array()) {
        $aRequest = array(
            'ACTION' => $action
        );

        if (isset($parameters['MODE'])) {
            $aRequest['MODE'] = $parameters['MODE'];
            unset($parameters['MODE']);
        }

        if (isset($parameters['iCachingTimeLimit'])) {
            unset($parameters['iCachingTimeLimit']);
        }

        if (!empty($parameters)) {
            $aRequest = array_merge($aRequest, $parameters);
        }

        if (MLRequest::gi()->data('maxitems') !== null && (int)MLRequest::gi()->data('steps') > 0) {
            $aRequest['steps'] = (int)MLRequest::gi()->data('steps');
        }

        return $aRequest;
    }

    public function execute() {
        foreach ($this->getListOfAPICallAndParameters() as $aCachedCall) {
            $action = $aCachedCall['action'];
            $parameters = isset($aCachedCall['parameters']) ? $aCachedCall['parameters'] : array();
            $parameters = $this->manipulateParameters($action, $parameters);
            $aRequest = $this->getDataRequest($action, $parameters);
            $this->log($action.':'.json_encode($parameters)."\n\n", self::LOG_LEVEL_LOW);
            $this->aRequestTimeouts[$action] = 0;
            if (isset($parameters['iCachingTimeLimit'])) {
                $this->iCacheTimeLimit = $parameters['iCachingTimeLimit'];
            }

            try {
                MagnaConnector::gi()->setTimeOutInSeconds($this->aRequestTimeouts[$action]);
                if ($action === 'IsAuthed') {
                    // IsAuthed have different caching logic tha other API calls
                    MLModule::gi()->isAuthed(true);
                    $this->log(
                        'IsAuthed is successfully cached'."\n",
                        self::LOG_LEVEL_LOW
                    );
                } else {
                    $aResponse = MagnaConnector::gi()->submitRequestCached($aRequest, $this->iCacheTimeLimit,true);
                    $entriesCount = 1;
                    if (!empty($aResponse['DATA'])) {
                        $entriesCount = count($aResponse['DATA']);
                    }
                    $this->log(
                        'Received '.$entriesCount.' entities '.
                        'in  '.microtime2human($aResponse['Client']['Time'])."\n",
                        self::LOG_LEVEL_LOW
                    );
                }

                //Finished Caching
                $this->log('Finished Caching '.$action.' API call'."\n\n", self::LOG_LEVEL_LOW);

            } catch (MagnaException $oEx) {
                $this->log($oEx->getMessage(), self::LOG_LEVEL_MEDIUM);
            } catch (Exception $oEx) {
                $this->log($oEx->getMessage(), self::LOG_LEVEL_MEDIUM);
            }
        }

        //Finished Caching
        $this->log('Finished Caching of All API calls'."\n\n", self::LOG_LEVEL_LOW);
//        echo print_m(MagnaConnector::gi()->getTimePerRequest())."\n\n";

        return $this;
    }

    protected function manipulateParameters($action, $parameters) {
        return $parameters;
    }

    protected function out($mValue) {
        if (!MLHttp::gi()->isAjax()) {
            echo is_array($mValue) ? "\n{#" . base64_encode(json_encode(array_merge(array('Marketplace' => MLModule::gi()->getMarketPlaceName(), 'MPID' => MLModule::gi()->getMarketPlaceId(),), $mValue))) . "#}\n\n" : $mValue . "\n";
            flush();
        } else {
            if (isset($mValue['Done']) && $mValue['Finished'] && $mValue['Total'] ) {
                MLModule::gi()->setConfig('importcategoriesoffset', $mValue['Done']);
                MLSetting::gi()->add(
                    'aAjax',
                    array(
                        'success' => $mValue['Finished'],
                        'error' => '',
                        'offset' => $mValue['Done'],
                        'info' => array(
                            'total' => $mValue['Total'],
                            'current' => $mValue['Done'],
                            'purge' => false,
                        ),
                    )
                );
            }
        }

        return $this;
    }

    protected function getListOfAPICallAndParameters() {
        return $this->aCachedCalls;
    }

}
