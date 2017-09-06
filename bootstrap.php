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

require_once 'base.php';

// NOTE: Presence of the correct UNC classes needs to be ensured before any shop classes can be used.
\OxidEsales\TestingLibrary\TestConfig::prepareUnifiedNamespaceClasses();

define('OXID_PHP_UNIT', true);

$sTestFilePath = strtolower(end($_SERVER['argv']));
$sTestType = 'unit';
foreach (array('acceptance', 'selenium', 'javascript') as $search) {
    if (strpos($sTestFilePath, $search) !== false) {
        $sTestType = 'acceptance';
        break;
    }
}

switch($sTestType) {
    case 'acceptance':
        $bootstrap = new OxidEsales\TestingLibrary\Bootstrap\SeleniumBootstrap();
        break;
    default:
        $bootstrap = new OxidEsales\TestingLibrary\Bootstrap\UnitBootstrap();
        break;
}

$bootstrap->init();
