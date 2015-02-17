<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

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

$oServiceCaller = new oxServiceCaller();
$oServiceCaller->setParameter('cl', 'oxConfig');
$oServiceCaller->setParameter('fnc', 'getEdition');
$edition = $oServiceCaller->callService('ShopObjectConstructor', 1);
define("SHOP_EDITION", ($edition == 'EE') ? 'EE' : 'PE_CE');

require_once TEST_LIBRARY_PATH . '/bootstrap/prepareDbForUsage.php';

if (SELENIUM_SCREENSHOTS_PATH && !is_dir(SELENIUM_SCREENSHOTS_PATH)) {
    mkdir(SELENIUM_SCREENSHOTS_PATH, 0777, true);
}

require_once TEST_LIBRARY_PATH .'/oxAcceptanceTestCase.php';
