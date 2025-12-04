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

/**
 * Class for handling filesystem.
 * it find files in cascading filesystem
 */
class MLView {

    /**
     * Include a view and return the output as a string.
     *
     * @param string $sViewName The name of the view to include.
     * @param array $aVars The variables to pass to the views.
     */
    public static function includeViewScoped($sViewName, $aVars){
        extract(func_get_arg(1));
        include func_get_arg(0);
    }

    /**
     * Include a view and return the output as a string.
     *
     * @param array $aViewNames The names of the views to include.
     * @param array $aVars The variables to pass to the views.
     * @param bool $blAddFileErrorToMessage Whether to add file errors to the message.
     * @param string $ident The identifier for the view. e.g. marketplace name, shop-system name to differentiate between views with the same name.
     */
    public static function includeView( $aViewNames=array(), $aVars=array(), $blAddFileErrorToMessage=true, $ident=''){
        $aViewNames=is_string($aViewNames)?array($aViewNames):$aViewNames;
        $aViewNames=count($aViewNames)==0?array($ident):$aViewNames;
        $aPossibleViewNames=array();
        foreach($aViewNames as $sViewName){
            $sViewName=  strtolower($sViewName);
            if(substr($sViewName,0,strpos($sViewName,'_'))=='widget'){//starts with widget_
                $sIdent=  strtolower($ident).'_';
                while($sIdent=substr($sIdent,0, strrpos($sIdent, '_'))){
                    $aPossibleViewNames[]=$sViewName.'_'.$sIdent;
                }
            }
            $aPossibleViewNames[]=$sViewName;
        }
        $aExtract=array();
        foreach($aVars as $sKey=>$sValue){
            if($sKey!='this'){
                $aExtract[$sKey]=$sValue;
            }
        }
        foreach ($aPossibleViewNames as $sView) {
            unset($oFileEx);//dont rethrow
            try {
                $blDebug = MLSetting::gi()->get('blTemplateDebug') && substr($sView, strrpos($sView, '_')) != '_snippet';
                $sFile = MLFilesystem::gi()->getViewPath($sView);
                //                new dBug(array($sView=>$aPossibleViewNames));
                if ($blDebug) {
                    echo '<div data-content="controller: '.strtolower($ident).' | view: '.$sView.'">';
                    $time = microtime(true);
                }

                self::includeViewScoped($sFile, $aExtract);


                if ($blDebug) {
                    $executed_time = microtime(true) - $time;
                    echo '<div style="display: inline;">'.$sFile.': '.microtime2human($executed_time).'</div>';
                    echo '</div>';
                }
                break;
            } catch (ML_Filesystem_Exception $oFileEx) {

            } catch (Exception $oEx) {
                MLMessage::gi()->addNotice($oEx);
            }
        }
        if(isset($oFileEx)&&$blAddFileErrorToMessage){
            MLMessage::gi()->addNotice($oFileEx);
        }
    }

    /**
     * Include a view and return the output as a string.
     *
     * @param array $aViewNames The names of the views to include.
     * @param array $aVars The variables to pass to the views.
     * @param bool $blAddFileErrorToMessage Whether to add file errors to the message.
     * @param string $ident The identifier for the view.
     * @return string The output of the included view.
     */
    public static function includeViewBuffered($aViewNames, $aVars, $blAddFileErrorToMessage, $ident){
        ob_start();
        self::includeView($aViewNames, $aVars, $blAddFileErrorToMessage, $ident);
        return ob_get_clean();
    }

    /**
     * Add a tooltip to a field by its ID.
     *
     * @param string $fieldId The HTML ID of the field to add the tooltip to.
     * @param string $tooltip The tooltip text to add.
     */
    public static function addTooltipById($fieldId, $tooltip){
        MLView::includeView('widget_form_type_tooltip', ['aField' => ['id' => $fieldId, 'i18n' => ['tooltip' => $tooltip]]]);
    }
}
