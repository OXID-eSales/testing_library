<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

require_once TEST_LIBRARY_PATH . '/test_config.inc.php';

require_once TEST_LIBRARY_PATH.'vendor/autoload.php';

define('hostUrl', getenv('SELENIUM_SERVER')? getenv('SELENIUM_SERVER') : $sSeleniumServerIp );
define('browserName', getenv('BROWSER_NAME')? getenv('BROWSER_NAME') : $sBrowserName );

$sShopUrl = getenv('SELENIUM_TARGET')? getenv('SELENIUM_TARGET') : $sShopUrl;

define ( 'SELENIUM_SCREENSHOTS_PATH', getenv('SELENIUM_SCREENSHOTS_PATH')? getenv('SELENIUM_SCREENSHOTS_PATH') : $sSeleniumScreenShotsPath );
define ( 'SELENIUM_SCREENSHOTS_URL', getenv('SELENIUM_SCREENSHOTS_URL')? getenv('SELENIUM_SCREENSHOTS_URL') : $sSeleniumScreenShotsUrl );

if (SELENIUM_SCREENSHOTS_PATH && !is_dir(SELENIUM_SCREENSHOTS_PATH)) {
    mkdir(SELENIUM_SCREENSHOTS_PATH, 0777, 1);
}

if (getenv('OXID_LOCALE') == 'international') {
    define('oxTESTSUITEDIR', 'acceptanceInternational');
} else {
    define('oxTESTSUITEDIR', 'acceptance');
}

if (INSTALLSHOP) {
    $oCurl = new oxTestCurl();
    $oCurl->setUrl(shopURL . '/Services/_db.php');
    $oCurl->setParameters(array(
            'serial' => TEST_SHOP_SERIAL,
            'addDemoData' => 1,
            'turnOnVarnish' => OXID_VARNISH,
            'setupPath' => SHOP_SETUP_PATH,
        ));
    $sResponse = $oCurl->execute();
}