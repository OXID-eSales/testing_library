<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

require_once TEST_LIBRARY_PATH."/bootstrap/bootstrap_config.php";

if (!defined('oxPATH') || oxPATH == '') {
    die('Path to tested shop (oxPATH) is not defined');
}

require_once TEST_LIBRARY_PATH.'oxServiceCaller.php';
require_once TEST_LIBRARY_PATH.'oxFileCopier.php';

if (file_exists(TESTS_DIRECTORY.'vendor/autoload.php')) {
    require_once TESTS_DIRECTORY.'vendor/autoload.php';
} else {
    require_once TESTS_DIRECTORY.'../../autoload.php';
}

if (COPY_SERVICES_TO_SHOP) {
    $oFileCopier = new oxFileCopier();
    $sTarget = REMOTE_DIR ? REMOTE_DIR.'/Services' : oxPATH.'/Services';
    $oFileCopier->copyFiles(TEST_LIBRARY_PATH .'Services', $sTarget, true);
}

if (RESTORE_SHOP_AFTER_TEST_SUITE) {
    // dumping original database
    $oServiceCaller = new oxServiceCaller();
    $oServiceCaller->setParameter('dumpDB', true);
    $oServiceCaller->setParameter('dump-prefix', 'orig_db_dump');
    try {
        $oServiceCaller->callService('ShopPreparation', 1);
    } catch (Exception $e) {
        define('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR', true);
    }
}

if (defined('oxCCTempDir')) {
    $oFileCopier = new oxFileCopier();
    $oFileCopier->createEmptyDirectory(oxCCTempDir);
}

function getTestsBasePath()
{
    return TESTS_DIRECTORY;
}

register_shutdown_function(function () {
    if (RESTORE_SHOP_AFTER_TEST_SUITE && !defined('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR')) {
        $oServiceCaller = new oxServiceCaller();
        $oServiceCaller->setParameter('restoreDB', true);
        $oServiceCaller->setParameter('dump-prefix', 'orig_db_dump');
        $oServiceCaller->callService('ShopPreparation', 1);
    }
});
