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
        'addDemoData' => 0,
        'turnOnVarnish' => OXID_VARNISH,
        'importSql' => SHOP_TESTS_PATH . '/testsql/testdata' . OXID_VERSION_SUFIX . '.sql',
        'setupPath' => SHOP_SETUP_PATH,
    ));
    $sResponse = $oCurl->execute();
}

require_once TEST_LIBRARY_PATH . '/bootstrap/prepareDbForUsage.php';

require_once TESTS_DIRECTORY . '/additional.inc.php';
require_once TEST_LIBRARY_PATH . "/oxTestModuleLoader.php";

if (defined('SHOP_RESTORATION_CLASS') && file_exists(TEST_LIBRARY_PATH .'dbRestore/'.SHOP_RESTORATION_CLASS . ".php")) {
    include_once TEST_LIBRARY_PATH .'dbRestore/'. SHOP_RESTORATION_CLASS . ".php";
} else {
    include_once TEST_LIBRARY_PATH .'dbRestore/'. "dbRestore.php";
}

define('oxADMIN_LOGIN', oxDb::getDb()->getOne("select OXUSERNAME from oxuser where oxid='oxdefaultadmin'"));
define('oxADMIN_PASSWD', getenv('oxADMIN_PASSWD') ? getenv('oxADMIN_PASSWD') : 'admin');

require_once TEST_LIBRARY_PATH .'/oxUnitTestCase.php';
