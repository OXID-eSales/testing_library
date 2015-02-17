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

define('TESTS_DIRECTORY', __DIR__ .'/');
chdir(TESTS_DIRECTORY);

define('TEST_LIBRARY_PATH', TESTS_DIRECTORY .'Library/');

$sTestFilePath = strtolower(end($_SERVER['argv']));
$sTestType = 'unit';
foreach (array('acceptance', 'selenium', 'javascript') as $search) {
    if (strpos($sTestFilePath, $search) !== false) {
        $sTestType = 'acceptance';
        break;
    }
}

require_once TEST_LIBRARY_PATH ."/bootstrap/bootstrap_base.php";

switch($sTestType) {
    case 'acceptance':
        include_once TEST_LIBRARY_PATH ."/bootstrap/bootstrap_selenium.php";
        break;
    default:
        include_once TEST_LIBRARY_PATH ."/bootstrap/bootstrap_unit.php";
        break;
}
