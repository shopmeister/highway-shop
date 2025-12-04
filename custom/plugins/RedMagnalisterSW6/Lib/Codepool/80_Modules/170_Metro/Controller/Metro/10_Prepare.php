<?php

class ML_Metro_Controller_Metro_Prepare extends ML_Tabs_Controller_Widget_Tabs_Filesystem_Abstract {

    public static function getTabTitle() {
        return MLI18n::gi()->get('ML_GENERIC_PREPARE');
    }

    public static function getTabActive() {
        return MLModule::gi()->isConfigured();
    }

    public static function getTabDefault() {
        return true;
    }
}
