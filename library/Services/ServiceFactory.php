<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
        $className = $serviceClass;
        if (!$this->isNamespacedClass($serviceClass)) {
            // Used for backwards compatibility.
            $className = $this->formClassName($serviceClass);
        }
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

    /**
     * @param string $className
     *
     * @return bool
     */
    private function isNamespacedClass($className)
    {
        return strpos($className, '\\') !== false;
    }
}
