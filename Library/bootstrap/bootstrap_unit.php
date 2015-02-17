<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

if (INSTALLSHOP) {
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

if (defined('SHOPRESTORATIONCLASS') && file_exists(TEST_LIBRARY_PATH .'dbRestore/'.SHOPRESTORATIONCLASS . ".php")) {
    include_once TEST_LIBRARY_PATH .'dbRestore/'. SHOPRESTORATIONCLASS . ".php";
} else {
    include_once TEST_LIBRARY_PATH .'dbRestore/'. "dbRestore.php";
}

define('oxADMIN_LOGIN', oxDb::getDb()->getOne("select OXUSERNAME from oxuser where oxid='oxdefaultadmin'"));
define('oxADMIN_PASSWD', getenv('oxADMIN_PASSWD') ? getenv('oxADMIN_PASSWD') : 'admin');

require_once TEST_LIBRARY_PATH .'/oxUnitTestCase.php';
