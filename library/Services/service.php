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

error_reporting(E_ALL);
ini_set('display_errors', '1');

$bootstrapPath = __DIR__ ."../bootstrap.php";
require_once $bootstrapPath;

require_once 'Library/ServiceConfig.php';
require_once 'Library/Request.php';
require_once 'ServiceCaller.php';

$request = new Request();
$config = new ServiceConfig();
$serviceCaller = new ServiceCaller($config);

$response = $serviceCaller->callService($request->getParameter('service'), $request);

echo serialize($response);