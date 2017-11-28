<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ClearCache;

use OxidEsales\TestingLibrary\Services\Library\Cache;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * This script clears shop cache
 */
class ClearCache implements ShopServiceInterface
{
    /** @var ServiceConfig */
    private $serviceConfig;

    /**
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->serviceConfig = $config;
    }

    /**
     * Clears shop cache.
     *
     * @param Request $request
     */
    public function init($request)
    {
        $cache = new Cache();
        if ($this->getServiceConfig()->getShopEdition() === ServiceConfig::EDITION_ENTERPRISE) {
            $cache->clearCacheBackend();
            if ($request->getParameter('clearVarnish')) {
                $cache->clearReverseProxyCache();
            }
        }
        $cache->clearTemporaryDirectory();
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->serviceConfig;
    }
}
