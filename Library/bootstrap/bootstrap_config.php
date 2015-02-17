<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

$configuration = TESTS_DIRECTORY ."test_config.yml";
if (!file_exists($configuration)) {
    $configuration = TESTS_DIRECTORY ."../../../test_config.yml";
}
$yaml = Symfony\Component\Yaml\Yaml::parse(file_get_contents($configuration));
$configuration = $yaml['parameters'];

define('OXID_PHP_UNIT', true);

define('oxPATH', realpath(TESTS_DIRECTORY ."../../../") .'/');
define('OX_BASE_PATH', oxPATH);
define('REMOTE_DIR', getenv('REMOTE_DIR')? getenv('REMOTE_DIR') : $configuration['remote_directory']);

$sTestsPath = $configuration['shop_tests_path'];
if (strpos($sTestsPath, '/') !== 0) {
    $sTestsPath = oxPATH .$sTestsPath;
}
define('SHOP_TESTS_PATH', rtrim($sTestsPath, '/') .'/');

define('MODULES_PATH', getenv('MODULES_PATH') ? getenv('MODULES_PATH') : $configuration['modules_path']);

define('INSTALLSHOP', getenv('oxINSTALLSHOP') !== false ? (bool)getenv('oxINSTALLSHOP') : $configuration['install_shop']);
define('ADD_TEST_DATA', getenv('oxSKIPSHOPSETUP') !== false ? (bool)!getenv('oxSKIPSHOPSETUP') : $configuration['add_test_data']);
define('RESTORE_SHOP_AFTER_TEST_SUITE', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $configuration['restore_shop_after_tests_suite']);
define('RESTORE_SHOP_AFTER_TEST', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $configuration['restore_shop_after_test']);

define('SHOP_SETUP_PATH', getenv('SHOP_SETUP_PATH') ? getenv('SHOP_SETUP_PATH') : $configuration['shop_setup_path']);

define('SHOPRESTORATIONCLASS', getenv('SHOPRESTORATIONCLASS') ? getenv('SHOPRESTORATIONCLASS') : $configuration['data_base_restore']);

define('OXID_VERSION', getenv('OXID_VERSION')); // only used for deploy test. If not set - package version is not checked.
define('TEST_SHOP_SERIAL', getenv('TEST_SHOP_SERIAL') ? getenv('TEST_SHOP_SERIAL') : $configuration['shop_serial']);
define('OXID_VARNISH', getenv('OXID_VARNISH') !== false ? (bool)getenv('OXID_VARNISH') : $configuration['enable_varnish']);

define('hostUrl', getenv('SELENIUM_SERVER')? getenv('SELENIUM_SERVER') : $configuration['selenium_server_ip']);
define('browserName', getenv('BROWSER_NAME')? getenv('BROWSER_NAME') : $configuration['browser_name']);


define ('SELENIUM_SCREENSHOTS_PATH', getenv('SELENIUM_SCREENSHOTS_PATH')? getenv('SELENIUM_SCREENSHOTS_PATH') : $configuration['selenium_screen_shots_path']);
define ('SELENIUM_SCREENSHOTS_URL', getenv('SELENIUM_SCREENSHOTS_URL')? getenv('SELENIUM_SCREENSHOTS_URL') : $configuration['selenium_screen_shots_url']);

if (file_exists(oxPATH . "/_version_define.php")) {
    include_once oxPATH . "/_version_define.php";
} else {
    define('OXID_VERSION_SUFIX', '');
}

if (OXID_VERSION_EE) :
    $sShopId = getenv('oxSHOPID') ? (int)getenv('oxSHOPID') : ($configuration['shop_id'] ? 2 : 1);
endif;
if (OXID_VERSION_PE) :
    $sShopId = "oxbaseshop";
endif;
define('oxSHOPID', $sShopId);

$sShopUrl = getenv('SELENIUM_TARGET') ? getenv('SELENIUM_TARGET') : $configuration['shop_url'];
if (!$sShopUrl) {
    include_once oxPATH . 'core/oxconfigfile.php';
    $oConfigFile = new oxConfigFile(oxPATH . "config.inc.php");
    $sShopUrl = $sShopUrl ? $sShopUrl : $oConfigFile->sShopURL;
}
define('shopURL', rtrim($sShopUrl, '/') . '/');

define('isSUBSHOP', is_int(oxSHOPID) ? oxSHOPID > 1 : false);

if ($configuration['special_db_dump_folder']) {
    define('oxCCTempDir', oxPATH . '/oxCCTempDir/');
}
