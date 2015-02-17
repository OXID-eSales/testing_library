<?php

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