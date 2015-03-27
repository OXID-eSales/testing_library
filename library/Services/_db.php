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

error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
ini_set('display_errors', true);

define('LIBRARY_PATH', __DIR__ .'/Library/');
define('TMP_PATH', __DIR__ .'/temp/');
define('SHOP_PATH', __DIR__ .'/../');

require_once LIBRARY_PATH . 'Request.php';
require_once 'ShopServiceInterface.php';

require_once __DIR__ .'/ShopInstaller/ShopInstaller.php';

$oShopInstaller = new ShopInstaller();

$sShopTestingSerial = array_key_exists('serial', $_REQUEST)? $_REQUEST['serial'] : false;
$blAddDemoData = array_key_exists('addDemoData', $_REQUEST) ? $_REQUEST['addDemoData'] : true;
$blInternationalShop = array_key_exists('international', $_REQUEST) ? $_REQUEST['international'] : false;
$blTurnOnVarnish = (bool)$oShopInstaller->turnOnVarnish || $_REQUEST['RP'] || $_REQUEST['turnOnVarnish'];
$sTestSqlLocalFile = array_key_exists('importSql', $_REQUEST) ? $_REQUEST['importSql'] : false;
$sTestSqlRemoteFile = array_key_exists('importSql', $_FILES) ? $_FILES['importSql'] : false;
$sSetupPath = array_key_exists('setupPath', $_REQUEST) ? $_REQUEST['setupPath'] : null;

if ($sSetupPath) {
    $oShopInstaller->setSetupDirectory($sSetupPath);
}

if ($sTestSqlRemoteFile) {
    include_once 'Library/FileUploader.php';
    $oFileUploader = new FileUploader();
    $sTestSqlLocalFile = 'temp/import.sql';
    $oFileUploader->uploadFile('importSql', $sTestSqlLocalFile);
}
if (!$sTestSqlLocalFile && $_REQUEST['test']) {
    $blAddDemoData = false;
    $sTestSqlLocalFile = '../../tests/testsql/testdata'.OXID_VERSION_SUFIX.'.sql';
}

if (!file_exists($oShopInstaller->getSetupDirectory() .'/sql'.OXID_VERSION_SUFIX)) {
    echo "Failed to install shop. Setup directory was not found!";
    http_response_code(500);
    exit(1);
}

?>

<h1>Full reinstall of OXID eShop</h1>

<ol>
    <li>drop and recreate database: <?=$oShopInstaller->dbName?> <?php $oShopInstaller->setupDatabase(); ?></li>
    <?php if ($blAddDemoData) : ?>
        <li>Insert demo data <?php $oShopInstaller->insertDemoData()?></li>
    <?php endif; ?>
    <?php if ($blInternationalShop) : ?>
        <li>Convert shop to International <?php $oShopInstaller->convertToInternational();?></li>
    <?php endif; ?>
    <?php if ($sTestSqlLocalFile) : ?>
        <li>Insert test data <?php $oShopInstaller->importFileToDatabase($sTestSqlLocalFile)?></li>
    <?php endif; ?>
    <li>Add configuration options <?php $oShopInstaller->setConfigurationParameters();?></li>
    <li>Set serial number to: <?=$sShopTestingSerial?><?php $oShopInstaller->setSerialNumber($sShopTestingSerial);?></li>
    <?php if ($oShopInstaller->iUtfMode) : ?>
        <li>Convert shop to UTF8 <?php $oShopInstaller->convertToUtf();?></li>
    <?php endif; ?>
    <?php if ($blTurnOnVarnish) : ?>
        <li>Turn on varnish <?php $oShopInstaller->turnVarnishOn();?></li>
    <?php endif; ?>
    <li>Delete cookies: <?php implode(', ', $oShopInstaller->deleteCookies()); ?></li>
    <li>Clear temp directory: <?=$oShopInstaller->sCompileDir?> <?php $oShopInstaller->clearTemp(); ?></li>
</ol>

<h3><a target='shp' href='<?=$oShopInstaller->sShopURL?>'>to Shop &raquo; </a></h3>
<h3><a target='adm' href='<?=$oShopInstaller->sShopURL?>/admin/'>to Admin &raquo; </a></h3>