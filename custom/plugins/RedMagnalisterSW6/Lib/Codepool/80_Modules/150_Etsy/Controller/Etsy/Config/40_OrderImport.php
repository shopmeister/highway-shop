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
MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_ConfigAbstract');
class ML_Etsy_Controller_Etsy_Config_OrderImport extends ML_Form_Controller_Widget_Form_ConfigAbstract {
    public static function getTabTitle () {
        return MLI18n::gi()->get('etsy_config_account_orderimport');
    }
    public static function getTabActive() {
        return self::calcConfigTabActive(__class__, false);
    }

    public function getI18nForm() {
        $aForm = $this->getFormArray()['aForm'];
        $i18nForm = [];

        foreach ($aForm as $items) {
            foreach ($items as $key => $item) {
                if ($key === 'fields') {
                    $i18nForm = array_merge($i18nForm, $item);
                }
            }
        }

        foreach ($i18nForm as $key => $value) {
            $i18nForm[$key] = [
                'label' => isset($value['i18n']['label']) ? $value['i18n']['label'] : null,
                'help' => isset($value['i18n']['help']) ? $value['i18n']['help'] : null,
                'name' => $value['name'],
                'type' => $value['type'],
            ];
        }

        return $i18nForm;
    }
}
