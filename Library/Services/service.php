<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__FILE__) . "/../bootstrap.php";
require_once 'ServiceCaller.php';
require_once 'ShopServiceInterface.php';

define('LIBRARY_PATH', dirname(__FILE__).'/Library/');
define('TEMP_PATH', dirname(__FILE__).'/temp/');
define('SHOP_PATH', dirname(__FILE__) . '/../');

if (!file_exists(TEMP_PATH)) {
    mkdir(TEMP_PATH, 0777);
    chmod(TEMP_PATH, 0777);
}

try {
    $oxConfig = oxRegistry::getConfig();

    $oServiceCaller = new ServiceCaller();

    $oServiceCaller->setActiveShop($oxConfig->getRequestParameter('shp'));
    $oServiceCaller->setActiveLanguage($oxConfig->getRequestParameter('lang'));
    $mResponse = $oServiceCaller->callService($oxConfig->getRequestParameter('service'));

    echo serialize($mResponse);
} catch (Exception $e) {
    echo "EXCEPTION: ".$e->getMessage();
}
