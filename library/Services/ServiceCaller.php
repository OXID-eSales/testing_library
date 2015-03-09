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

define('LIBRARY_PATH', __DIR__.'/Library/');

if (!defined('TEST_SERVICES_PATH')) {
    define('TEST_SERVICES_PATH', __DIR__);
}

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', TEST_SERVICES_PATH.'/temp/');
}

if (!defined('oxPATH')) {
    define('oxPATH', __DIR__ . '/../');
}

require_once oxPATH ."/bootstrap.php";

require_once 'Request.php';
require_once 'ShopServiceInterface.php';

if (!file_exists(TESTS_TEMP_DIR)) {
    mkdir(TESTS_TEMP_DIR, 0777);
    chmod(TESTS_TEMP_DIR, 0777);
}

/**
 * Class ServiceCaller
 */
class ServiceCaller
{
    /**
     * Calls service
     *
     * @param string $serviceClass
     * @param Request $request
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function callService($serviceClass, $request)
    {
        if (!is_null($request->getParameter('shp'))) {
            $this->setActiveShop($request->getParameter('shp'));
        }
        if (!is_null($request->getParameter('lang'))) {
            $this->setActiveShop($request->getParameter('lang'));
        }

        $service = $this->createService($serviceClass);

        return $service->init($request);
    }

    /**
     * Switches active shop
     *
     * @param string $shopId
     */
    public function setActiveShop($shopId)
    {
        $config = oxRegistry::getConfig();
        if ($shopId && $config->getEdition() == 'EE') {
            $config->setShopId($shopId);
        }
    }

    /**
     * Switches active language
     *
     * @param string $language
     *
     * @throws Exception
     */
    public function setActiveLanguage($language)
    {
        $languages = oxRegistry::getLang()->getLanguageIds();
        $languageId = array_search($language, $languages);
        if ($languageId === false) {
            throw new Exception("Language $language was not found or is not active in shop");
        }
        oxRegistry::getLang()->setBaseLanguage($languageId);
    }

    /**
     * Creates Service object. All services must implement ShopService interface
     *
     * @param string $serviceClass
     *
     * @throws Exception
     *
     * @return ShopServiceInterface
     */
    protected function createService($serviceClass)
    {
        $this->includeServiceFile($serviceClass);
        $service = new $serviceClass();

        if (!($service instanceof ShopServiceInterface)) {
            throw new Exception("Service $serviceClass does not implement ShopServiceInterface interface!");
        }

        return $service;
    }

    /**
     * Includes service main class file
     *
     * @param string $serviceClass
     *
     * @throws Exception
     */
    protected function includeServiceFile($serviceClass)
    {
        $file = realpath(TEST_SERVICES_PATH . '/' . $serviceClass . '/' . $serviceClass . '.php');

        if (!file_exists($file)) {
            throw new Exception("Service $serviceClass not found in path $file!");
        }

        include_once $file;
    }
}
