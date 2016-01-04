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
namespace OxidEsales\TestingLibrary\Services;

use Exception;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Services Factory class.
 */
class ServiceFactory
{
    /**
     * Loads the shop.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        include_once $config->getShopDirectory() . '/bootstrap.php';
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
    public function createService($serviceClass)
    {
        $className = $this->formClassName($serviceClass);
        if (!class_exists($className)) {
            throw new Exception("Service '$serviceClass' was not found!");
        }
        $service = new $className($this->getServiceConfig());

        if (!($service instanceof ShopServiceInterface)) {
            throw new Exception("Service '$className' does not implement ShopServiceInterface interface!");
        }

        return $service;
    }

    /**
     * Includes service main class file
     *
     * @param string $serviceClass
     *
     * @return string
     */
    protected function formClassName($serviceClass)
    {
        return "OxidEsales\\TestingLibrary\\Services\\$serviceClass\\$serviceClass";
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->config;
    }
}
