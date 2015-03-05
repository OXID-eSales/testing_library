<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
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
