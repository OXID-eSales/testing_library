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

$oFileCopier = new oxFileCopier();
$sTarget = REMOTE_DIR ? REMOTE_DIR.'/Services' : oxPATH.'/Services';
$oFileCopier->copyFiles(TEST_LIBRARY_PATH .'Services', $sTarget, true);

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
    return SHOP_TESTS_PATH;
}

register_shutdown_function(function () {
    if (RESTORE_SHOP_AFTER_TEST_SUITE && !defined('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR')) {
        $oServiceCaller = new oxServiceCaller();
        $oServiceCaller->setParameter('restoreDB', true);
        $oServiceCaller->setParameter('dump-prefix', 'orig_db_dump');
        $oServiceCaller->callService('ShopPreparation', 1);
    }
});
