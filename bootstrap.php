<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
