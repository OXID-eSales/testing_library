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

require_once TEST_LIBRARY_PATH .'test_utils.php';
require_once oxPATH .'core/oxfunctions.php';

$oConfigFile = new oxConfigFile(oxPATH . "config.inc.php");
OxRegistry::set("OxConfigFile", $oConfigFile);
oxRegistry::set("oxConfig", new oxConfig());
if ($sTestType == 'acceptance') {
    oxRegistry::set("oxConfig", oxNew('oxConfig'));
}

$oDb = new oxDb();
$oDb->setConfig($oConfigFile);
$oLegacyDb = $oDb->getDb();
OxRegistry::set('oxDb', $oLegacyDb);

oxRegistry::getConfig();

require_once TEST_LIBRARY_PATH .'modOxUtilsDate.php';
require_once oxPATH .'/core/oxutils.php';
require_once oxPATH .'/core/adodblite/adodb.inc.php';
require_once oxPATH .'/core/oxsession.php';
require_once oxPATH .'/core/oxconfig.php';