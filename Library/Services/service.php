<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('LIBRARY_PATH', dirname(__FILE__).'/Library/');
define('TEMP_PATH', dirname(__FILE__).'/temp/');
define('SHOP_PATH', dirname(__FILE__) . '/../');

require_once dirname(__FILE__) . "/../bootstrap.php";
require_once 'ServiceCaller.php';
require_once LIBRARY_PATH . 'Request.php';
require_once 'ShopServiceInterface.php';

if (!file_exists(TEMP_PATH)) {
    mkdir(TEMP_PATH, 0777);
    chmod(TEMP_PATH, 0777);
}

try {
    $request = new Request();

    $oServiceCaller = new ServiceCaller();

    try {
        $oServiceCaller->setActiveShop($request->getParameter('shp'));
        $oServiceCaller->setActiveLanguage($request->getParameter('lang'));
    } catch (Exception $e) {
        // do nothing even if exception was caught during setting of language or shop
    }

    $mResponse = $oServiceCaller->callService($request->getParameter('service'));

    echo serialize($mResponse);
} catch (Exception $e) {
    echo "EXCEPTION: ".$e->getMessage();
}
