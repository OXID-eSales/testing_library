<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
ini_set('display_errors', true);

$sTestType = substr(getcwd(), strlen(__DIR__)+1);

define('TESTS_DIRECTORY', rtrim(__DIR__, '/').'/');

chdir(TESTS_DIRECTORY);

define('TEST_LIBRARY_PATH', rtrim(realpath('Library'), '/').'/');

if ($sTestType && strpos($sTestType, '/')) {
    $sTestType = substr($sTestType, 0, strpos($sTestType, '/'));
}

if (empty($sTestType)) {
    $sTestType = basename(end($_SERVER['argv']));
    $sTestType = str_replace('.php', '', $sTestType);
    $sTestType = strtolower(substr($sTestType, 8));
    reset($_SERVER['argv']);
}

require_once TEST_LIBRARY_PATH."bootstrap/bootstrap_config.php";
require_once TEST_LIBRARY_PATH."bootstrap/bootstrap_base.php";

switch($sTestType) {
    case 'acceptance':
    case 'selenium':
    case 'javascript':
        include_once TEST_LIBRARY_PATH."bootstrap/bootstrap_selenium.php";
        break;
    default:
        include_once TEST_LIBRARY_PATH."bootstrap/bootstrap_unit.php";
        break;
}
