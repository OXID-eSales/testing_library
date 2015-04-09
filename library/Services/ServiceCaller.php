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

define('LIBRARY_PATH', __DIR__ .'/Library/');
require_once LIBRARY_PATH .'/ShopServiceInterface.php';

/**
 * Class ServiceCaller
 */
class ServiceCaller
{
    /**
     * Loads the shop.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        $bootstrapPath = $this->getServiceConfig()->getShopDirectory() ."/bootstrap.php";
        if (file_exists($bootstrapPath)) {
            require_once $bootstrapPath;
        }
    }

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
            $this->setActiveLanguage($request->getParameter('lang'));
        }

        $service = $this->createService($serviceClass);

        return $service->init($request);
    }

    /**
     * Switches active shop
     *
     * @param string $shopId
     */
    protected function setActiveShop($shopId)
    {
        if ($shopId && $this->getServiceConfig()->getShopEdition() == 'EE') {
            oxRegistry::getConfig()->setShopId($shopId);
        }
    }

    /**
     * Switches active language
     *
     * @param string $language
     *
     * @throws Exception
     */
    protected function setActiveLanguage($language)
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
        $service = new $serviceClass($this->getServiceConfig());

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
        $servicesDirectory = $this->getServiceConfig()->getServicesDirectory();
        $file = "$servicesDirectory/$serviceClass/$serviceClass.php";

        if (!file_exists($file)) {
            throw new Exception("Service $serviceClass not found in path $file!");
        }

        include_once $file;
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->config;
    }
}
