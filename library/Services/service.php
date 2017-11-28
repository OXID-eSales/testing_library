<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\ServiceFactory;

error_reporting(E_ALL);
ini_set('display_errors', '1');

spl_autoload_register(function($className) {
    if (strpos($className, 'OxidEsales\\TestingLibrary\\Services\\') !== false) {
        $class = substr($className, 35);
        $filePath = __DIR__.'/'.str_replace('\\', '/', $class).'.php';
        if (file_exists($filePath)) {
            include_once $filePath;
        }
    }
});

// We need the composer autoloader.
$installationRootPath =  dirname(dirname(dirname(__FILE__)));
$vendorDirectory = $installationRootPath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
require_once $vendorDirectory . 'autoload.php';

// Generate UNC classes before bootstrapping the shop
\OxidEsales\TestingLibrary\TestConfig::prepareUnifiedNamespaceClasses();

// Bootstrap the shop framework
require_once __DIR__ ."../../bootstrap.php";

/** This constant should only be used in TestConfig class. Use TestConfig::getVendorPath() instead. */
define('TEST_LIBRARY_VENDOR_DIRECTORY', $vendorDirectory);

$request = new Request();
$config = new ServiceConfig(__DIR__ . '/../');
$serviceFactory = new ServiceFactory($config);

$service = $serviceFactory->createService($request->getParameter('service'));
$response = $service->init($request);

echo serialize($response);
