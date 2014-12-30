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
        'importSql' => TESTS_DIRECTORY . 'testsql/testdata' . OXID_VERSION_SUFIX . '.sql',
        'setupPath' => SHOP_SETUP_PATH,
    ));
    $sResponse = $oCurl->execute();
}

require_once TEST_LIBRARY_PATH . '/test_config.inc.php';
require_once TEST_LIBRARY_PATH . 'vendor/autoload.php';
require_once "unit/OxidTestCase.php";
require_once TEST_LIBRARY_PATH . '/additional.inc.php';
require_once TEST_LIBRARY_PATH . "/oxTestModuleLoader.php";

if (defined('SHOPRESTORATIONCLASS') && file_exists(TEST_LIBRARY_PATH . SHOPRESTORATIONCLASS . ".php")) {
    include_once TEST_LIBRARY_PATH . SHOPRESTORATIONCLASS . ".php";
} else {
    include_once TEST_LIBRARY_PATH . "dbRestore.php";
}

define('oxADMIN_LOGIN', oxDb::getDb()->getOne("select OXUSERNAME from oxuser where oxid='oxdefaultadmin'"));
define('oxADMIN_PASSWD', getenv('oxADMIN_PASSWD') ? getenv('oxADMIN_PASSWD') : 'admin');


if (RESTORE_SHOP_AFTER_TEST_SUITE) {
    $oDbRestore = new DbRestore();
    $oDbRestore->restoreDB();
  //  define('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR', true);
}

register_shutdown_function(function () {
    if (RESTORE_SHOP_AFTER_TEST_SUITE) {
        $oDbRestore = new DbRestore();
        $oDbRestore->restoreDB();
    }
});
