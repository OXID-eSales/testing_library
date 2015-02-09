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
require_once TEST_LIBRARY_PATH.'vendor/autoload.php';

if(defined(oxCCTempDir)) {
    $oFileCopier = new oxFileCopier();
    $oFileCopier->createEmptyDirectory(oxCCTempDir);
}

function getTestsBasePath()
{
    return TESTS_DIRECTORY;
}

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

require_once TEST_LIBRARY_PATH .'/modOxUtilsDate.php';
require_once oxPATH .'/core/oxutils.php';
require_once oxPATH .'/core/adodblite/adodb.inc.php';
require_once oxPATH .'/core/oxsession.php';
require_once oxPATH .'/core/oxconfig.php';

if (COPY_SERVICES_TO_SHOP) {
    $oFileCopier = new oxFileCopier();
    $sTarget = REMOTE_DIR ? REMOTE_DIR.'/Services' : oxPATH.'/Services';
    $oFileCopier->copyFiles(TEST_LIBRARY_PATH.'/Services', $sTarget, true);
}
