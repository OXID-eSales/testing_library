<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ModuleInstaller;


use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\TestingLibrary\ModuleLoader;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Class for module installation.
 */
class ModuleInstaller implements ShopServiceInterface
{
    /**
     * @param ServiceConfig $config
     */
    public function __construct($config) {}

    /**
     * Starts installation of the shop.
     *
     * @param Request $request
     *
     */
    public function init($request)
    {
        if (($shopId = $request->getParameter('shp')) && (1 < $shopId)) {
            $this->switchToShop($shopId);
        }

        (new ModuleLoader())->activateModules($request->getParameter("modulestoactivate"));
    }

    /**
     * Switch to subshop.
     * 
     * @param integer $shopId
     *
     * @return integer
     */
    public function switchToShop($shopId)
    {
        $_POST['shp'] = $shopId;
        $_POST['actshop'] = $shopId;
        $keepThese = [\OxidEsales\Eshop\Core\ConfigFile::class];
        $registryKeys = Registry::getKeys();
        foreach ($registryKeys as $key) {
            if (in_array($key, $keepThese)) {
                continue;
            }
            Registry::set($key, null);
        }
        $utilsObject = new \OxidEsales\Eshop\Core\UtilsObject;
        $utilsObject->resetInstanceCache();
        Registry::set(\OxidEsales\Eshop\Core\UtilsObject::class, $utilsObject);
        \OxidEsales\Eshop\Core\Module\ModuleVariablesLocator::resetModuleVariables();
        Registry::getSession()->setVariable('shp', $shopId);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, null);
        $moduleVariablesCache = new \OxidEsales\Eshop\Core\FileCache();
        $shopIdCalculator = new \OxidEsales\Eshop\Core\ShopIdCalculator($moduleVariablesCache);
        return  $shopIdCalculator->getShopId();
    }
}
