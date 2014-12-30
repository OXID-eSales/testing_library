<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

require_once TESTS_DIRECTORY . "test_config.php";
if (file_exists(TESTS_DIRECTORY . "test_config.local.php")) {
    include_once TESTS_DIRECTORY . "test_config.local.php";
}

define('OXID_PHP_UNIT', true);

$sShopPath = getenv('oxPATH') ? getenv('oxPATH') : $sShopPath;
define('oxPATH', rtrim($sShopPath, '/') . '/');

define('INSTALLSHOP', getenv('oxINSTALLSHOP') !== false ? (bool)getenv('oxINSTALLSHOP') : $blInstallShop);
define('ADD_TEST_DATA', getenv('oxSKIPSHOPSETUP') !== false ? (bool)!getenv('oxSKIPSHOPSETUP') : $blAddTestData);
define('RESTORE_SHOP_AFTER_TEST_SUITE', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $blRestoreShopAfterTestSuite);
define('RESTORE_SHOP_AFTER_TEST', getenv('oxSKIPSHOPRESTORE') !== false ? (bool)!getenv('oxSKIPSHOPRESTORE') : $blRestoreShopAfterTest);

if (!$sShopSetupPath) {
    $sShopSetupPath = oxPATH . 'setup/';
}
define('SHOP_SETUP_PATH', getenv('SHOP_SETUP_PATH') ? getenv('SHOP_SETUP_PATH') : $sShopSetupPath);
define('MODULES_PATH', getenv('MODULES_PATH') ? getenv('MODULES_PATH') : $sModulesPath);

define('SHOPRESTORATIONCLASS', getenv('SHOPRESTORATIONCLASS') ? getenv('SHOPRESTORATIONCLASS') : $sDataBaseRestore);
define('COPY_SERVICES_TO_SHOP', getenv('COPY_SERVICES_TO_SHOP') !== false ? (bool)getenv('COPY_SERVICES_TO_SHOP') : $blCopyServicesToShop);

define('OXID_VERSION', getenv('OXID_VERSION')); // only used for deploy test. If not set - package version is not checked.
define('TEST_SHOP_SERIAL', getenv('TEST_SHOP_SERIAL') ? getenv('TEST_SHOP_SERIAL') : $sShopSerial);
define('OXID_VARNISH', getenv('OXID_VARNISH') !== false ? (bool)getenv('OXID_VARNISH') : $blVarnish);

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
define('oxSHOPID', $sShopId);

$sShopUrl = getenv('SELENIUM_TARGET') ? getenv('SELENIUM_TARGET') : $sShopUrl;
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