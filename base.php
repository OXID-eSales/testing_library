<?php

$travisErrorLevel = getenv('TRAVIS_ERROR_LEVEL');
if ($travisErrorLevel !== false) {
    error_reporting((int)$travisErrorLevel);
} else {
    error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
}
ini_set('display_errors', true);

chdir(__DIR__);

/** @deprecated do not use this constant. Use normal class autoloading instead. */
define('TEST_LIBRARY_PATH', __DIR__ .'/library/');
define('TEST_LIBRARY_HELPERS_PATH', TEST_LIBRARY_PATH .'helpers/');

$vendorDirectory = __DIR__ . "/../../../vendor/";
if (!file_exists($vendorDirectory)) {
    $vendorDirectory = __DIR__ .'/vendor/';
}
/** This constant should only be used in TestConfig class. Use TestConfig::getVendorPath() instead. */
define('TEST_LIBRARY_VENDOR_DIRECTORY', $vendorDirectory);

require_once $vendorDirectory . 'autoload.php';