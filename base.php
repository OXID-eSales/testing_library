<?php
error_reporting(E_ALL);
ini_set('display_errors', "1");

$currentErrorReportingLevel = (int) error_reporting();
$travisErrorLevel = (int) getenv('TRAVIS_ERROR_LEVEL');
if ($travisErrorLevel !== 0 && $currentErrorReportingLevel !== $travisErrorLevel) {
    throw new Exception('Travis error reporting level defined in .travis.yml ('.$travisErrorLevel.') differs from current error reporting level in testing library (' .  $currentErrorReportingLevel . ')');
}

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