<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

if (!defined('oxPATH') || oxPATH == '') {
    die('Path to tested shop (oxPATH) is not defined');
}

require_once TEST_LIBRARY_PATH.'oxServiceCaller.php';
require_once TEST_LIBRARY_PATH.'oxFileCopier.php';

if (!is_dir(oxCCTempDir)) {
    mkdir(oxCCTempDir, 0777, 1);
} else {
    /**
     * Deletes given directory content
     *
     * @param string $dir       Path to directory.
     * @param bool   $rmBaseDir Whether to delete base directory.
     */
    function delTree($dir, $rmBaseDir = false)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file", true) : @unlink("$dir/$file");
        }
        if ($rmBaseDir) {
            @rmdir($dir);
        }
    }
    delTree(oxCCTempDir);
}

if (COPY_SERVICES_TO_SHOP) {
    $oFileCopier = new oxFileCopier();
    $oFileCopier->copyFiles(TEST_LIBRARY_PATH.'/Services', oxPATH.'/Services', true);
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

register_shutdown_function(function () {
    if (RESTORE_SHOP_AFTER_TEST_SUITE && !defined('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR')) {
        $oServiceCaller = new oxServiceCaller();
        $oServiceCaller->setParameter('restoreDB', true);
        $oServiceCaller->setParameter('dump-prefix', 'orig_db_dump');
        $oServiceCaller->callService('ShopPreparation', 1);
    }
});
