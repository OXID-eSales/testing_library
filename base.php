<?php

ini_set('display_errors', "1");

chdir(__DIR__);

/** @deprecated do not use this constant. Use normal class autoloading instead. */
define('TEST_LIBRARY_PATH', __DIR__ .'/library/');
define('TEST_LIBRARY_HELPERS_PATH', TEST_LIBRARY_PATH .'helpers/');
define('ACTIVE_THEME', getenv('ACTIVE_THEME') ? getenv('ACTIVE_THEME') : 'apex');

$vendorDirectory = __DIR__ . "/../../../vendor/";
if (!file_exists($vendorDirectory)) {
    $vendorDirectory = __DIR__ .'/vendor/';
}
/** This constant should only be used in TestConfig class. Use TestConfig::getVendorPath() instead. */
define('TEST_LIBRARY_VENDOR_DIRECTORY', $vendorDirectory);

require_once $vendorDirectory . 'autoload.php';
