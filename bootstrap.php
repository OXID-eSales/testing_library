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

if (getenv('TRAVIS_ERROR_LEVEL')) {
    error_reporting((int)getenv('TRAVIS_ERROR_LEVEL'));
} else {
    error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
}
ini_set('display_errors', true);

define('TESTS_DIRECTORY', __DIR__ .'/');
chdir(TESTS_DIRECTORY);

define('TEST_LIBRARY_PATH', TESTS_DIRECTORY .'Library/');
define('TESTING_LIBRARY_HELPERS_PATH', TEST_LIBRARY_PATH .'helpers/');

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
