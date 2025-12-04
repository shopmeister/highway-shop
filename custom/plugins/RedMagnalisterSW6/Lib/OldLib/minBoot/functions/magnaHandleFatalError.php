<?php
function magnaHandleFatalError() {
    $errorOccurred = false;
    if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
        $le = error_get_last();
        if (empty($le)) return;


        $condition = E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING |
            E_DEPRECATED | E_USER_DEPRECATED | (PHP_VERSION_ID < 80400 ? E_STRICT : 0);

        if (!($condition & $le['type'])) {
            echo '<pre>' . print_r($le, true) . '</pre>';
            $errorOccurred = true;
        }
    } else {
        global $php_errormsg;
        if (empty($php_errormsg)) return;
        echo '<pre>'.$php_errormsg.'</pre>';
        $errorOccurred = true;
    }
    if ($errorOccurred) {
        if (version_compare(PHP_VERSION, '5.2.5', '>=')) {
            echo '<pre>'.print_r(debug_backtrace(false), true).'</pre>';
        } else {
            echo '<pre>'.print_r(debug_backtrace(), true).'</pre>';
        }
    }
}