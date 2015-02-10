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

define('INSTALLSHOP', getenv('oxINSTALLSHOP') !== false ? (bool)getenv('oxINSTALLSHOP') : $configuration['install_shop']);
define('ADD_TEST_DATA', getenv('oxSKIPSHOPSETUP') !== false ? (bool)!getenv('oxSKIPSHOPSETUP') : $configuration['add_test_data']);
define('RESTORE_SHOP_AFTER_TEST_SUITE', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $configuration['restore_shop_after_tests_suite']);
define('RESTORE_SHOP_AFTER_TEST', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $configuration['restore_shop_after_test']);

define('SHOP_SETUP_PATH', getenv('SHOP_SETUP_PATH') ? getenv('SHOP_SETUP_PATH') : $configuration['shop_setup_path']);
define('MODULES_PATH', getenv('MODULES_PATH') ? getenv('MODULES_PATH') : $configuration['modules_path']);

define('SHOPRESTORATIONCLASS', getenv('SHOPRESTORATIONCLASS') ? getenv('SHOPRESTORATIONCLASS') : $configuration['data_base_restore']);
define('COPY_SERVICES_TO_SHOP', getenv('COPY_SERVICES_TO_SHOP') !== false ? (bool)getenv('COPY_SERVICES_TO_SHOP') : $configuration['copy_services_to_shop']);

define('OXID_VERSION', getenv('OXID_VERSION')); // only used for deploy test. If not set - package version is not checked.
define('TEST_SHOP_SERIAL', getenv('TEST_SHOP_SERIAL') ? getenv('TEST_SHOP_SERIAL') : $configuration['shop_serial']);
define('OXID_VARNISH', getenv('OXID_VARNISH') !== false ? (bool)getenv('OXID_VARNISH') : $configuration['enable_varnish']);

if (file_exists(oxPATH . "/_version_define.php")) {
    include_once oxPATH . "/_version_define.php";
} else {
    define('OXID_VERSION_SUFIX', '');
}

if (!defined('oxPATH')) {
    die('Path to tested shop (oxPATH) is not defined');
}

if (OXID_VERSION_EE) :
    $sShopId = getenv('oxSHOPID') ? (int)getenv('oxSHOPID') : ($blIsSubShop ? 2 : 1);
endif;
if (OXID_VERSION_PE) :
    $sShopId = "oxbaseshop";
endif;
define('oxSHOPID', $configuration['shop_id']);

$sShopUrl = getenv('SELENIUM_TARGET') ? getenv('SELENIUM_TARGET') : $configuration['shop_url'];
if (!$sShopUrl) {
    include_once oxPATH . 'core/oxconfigfile.php';
    $oConfigFile = new oxConfigFile(oxPATH . "config.inc.php");
    $sShopUrl = $sShopUrl ? $sShopUrl : $oConfigFile->sShopURL;
}
define('shopURL', rtrim($sShopUrl, '/') . '/');

$blIsSubShop = false;
if (OXID_VERSION_EE) :
    $blIsSubShop = oxSHOPID > 1;
endif;
define('isSUBSHOP', $blIsSubShop);

if ($blSpecialDbDumpFolder) {
    define('oxCCTempDir', oxPATH . '/oxCCTempDir/');
}