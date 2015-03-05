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

if (INSTALL_SHOP) {
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
