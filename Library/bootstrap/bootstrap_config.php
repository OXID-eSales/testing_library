<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

require_once TEST_LIBRARY_PATH.'/Test_Config.php';

$config = new Test_Config();

define('OXID_PHP_UNIT', true);

$shopPath = $config->getShopPath();
define('oxPATH', $shopPath);

if (file_exists($shopPath . "/_version_define.php")) {
    include_once $shopPath . "/_version_define.php";
} else {
    $edition = $config->getShopEdition();
    define('OXID_VERSION_EE', (int) ($edition == 'EE'));
    define('OXID_VERSION_PE_PE', (int) ($edition == 'PE'));
    define('OXID_VERSION_PE_CE', (int) ($edition == 'CE'));
    define('OXID_VERSION_PE', (int) ($edition != 'EE'));
    define('OXID_VERSION_SUFIX', '_ce');
}

define('OX_BASE_PATH', oxPATH);
define('REMOTE_DIR', $config->getRemoteDirectory());
define('shopURL', $config->getShopUrl());
define('oxSHOPID', $config->getShopId());

define('SHOP_TESTS_PATH', $config->getShopTestsPath());
define('MODULES_PATH', $config->getModulePaths());

define('INSTALL_SHOP', $config->shouldInstallShop());
define('RESTORE_SHOP_AFTER_TEST_SUITE', $config->shouldRestoreShopAfterTestsSuite());

define('SHOP_SETUP_PATH', $config->getShopSetupPath());

define('SHOP_RESTORATION_CLASS', $config->getDatabaseRestorationClass());

define('TEST_SHOP_SERIAL', $config->getShopSerial());
define('OXID_VARNISH', $config->shouldEnableVarnish());

define('SELENIUM_SERVER_IP', $config->getSeleniumServerIp());
define('browserName', $config->getBrowserName());

define ('SELENIUM_SCREENSHOTS_PATH', $config->getSeleniumScreenshotsPath());
define ('SELENIUM_SCREENSHOTS_URL', $config->getSeleniumScreenshotsUrl());

define('isSUBSHOP', $config->isSubShop());

if ($config->shouldUseSeparateDbDumpDirectory()) {
    define('oxCCTempDir', oxPATH . '/oxCCTempDir/');
}
