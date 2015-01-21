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

if(defined(oxCCTempDir)) {

    if (!is_dir(oxCCTempDir)) {
        mkdir(oxCCTempDir, 0777, 1);
    } else {
        /**
         * Deletes given directory content
         *
         * @param string $dir Path to directory.
         * @param bool $rmBaseDir Whether to delete base directory.
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
}

if (COPY_SERVICES_TO_SHOP) {
    $oFileCopier = new oxFileCopier();
    $sTarget = REMOTE_DIR ? REMOTE_DIR.'/Services' : oxPATH.'/Services';
    $oFileCopier->copyFiles(TEST_LIBRARY_PATH.'/Services', $sTarget, true);
}
