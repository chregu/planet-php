<?php
/**
 * This is a defaults configuration to ease configuration.
 *
 * You should not edit anything in here, just override the constants in
 * config.inc.php instead.
 *
 * @author Till Klampaeckel <till@php.net>
 */

/////////// POPOON RELATED
if (!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR', ":");
}
if (!defined('BX_PROJECT_DIR')) {
    define('BX_PROJECT_DIR', dirname(dirname(__FILE__)));
}
if (!defined('BX_POPOON_DIR')) {
    define('BX_POPOON_DIR', BX_PROJECT_DIR . '/libs/popoon/');
}
if (!defined('BX_INCLUDE_DIR')) {
    define('BX_INCLUDE_DIR', BX_PROJECT_DIR. '/libs/');
}
if (!defined('BX_TEMP_DIR')) {
    define('BX_TEMP_DIR', BX_PROJECT_DIR. 'tmp/');
}

// consider commenting this out and move this to php.ini or similar
ini_set('include_path',
    BX_INCLUDE_DIR . PATH_SEPARATOR
    . BX_POPOON_DIR . PATH_SEPARATOR
    . BX_PROJECT_DIR . '/library' . PATH_SEPARATOR
    . ini_get('include_path')
);
include_once BX_POPOON_DIR . '/autoload.php';


/////////// MAGPIE RELATED 
if (!defined('VERBOSE')) {
    define('VERBOSE', TRUE);
}
if (!defined('MAGPIE_CACHE_DIR')) {
    define('MAGPIE_CACHE_DIR', BX_PROJECT_DIR . '/tmp/magpie/');
}
if (!defined('MAGPIE_CACHE_AGE')) {
    define('MAGPIE_CACHE_AGE', 1000);
}
if (!defined('MAGPIE_USER_AGENT')) {
    define('MAGPIE_USER_AGENT', PROJECT_NAME . ' Aggregator/0.2 (PHP5) (' . PROJECT_URL . ')');
}

/**
 * Don't touch, this is necessary.
 */
include_once BX_POPOON_DIR . '/autoload.php';

// this global is somehow ugly...
$GLOBALS['POOL'] = popoon_pool::getInstance("popoon_classes_config");
$BX_config       = $GLOBALS['POOL']->config;

/**
 * For debugging.
 */
$BX_config->setOutputCacheCallback("checkOutputCaching");
function checkOutputCaching() {
    if ($_SERVER['REMOTE_ADDR'] == $GLOBALS['BX_config']['debugHost']) {
        return false;
    }
    return true;
}
