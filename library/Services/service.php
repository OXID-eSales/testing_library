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
