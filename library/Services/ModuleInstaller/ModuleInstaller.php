<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ModuleInstaller;


use Exception;
use OxidEsales\Eshop\Core\Registry;
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

        $modulesToActivate = $request->getParameter("modulestoactivate");
        $moduleDirectory = \OxidEsales\Eshop\Core\Registry::getConfig()->getModulesDir();

        $this->prepareModulesForActivation($moduleDirectory);
        foreach ($modulesToActivate as $modulePath) {
            $this->installModule($modulePath);
        }
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
        Registry::getConfig()->setConfig(null);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, null);
        $moduleVariablesCache = new \OxidEsales\Eshop\Core\FileCache();
        $shopIdCalculator = new \OxidEsales\Eshop\Core\ShopIdCalculator($moduleVariablesCache);
        return  $shopIdCalculator->getShopId();
    }

    /**
     * Activates module.
     *
     * @param string $modulePath The path to the module.
     *
     * @throws Exception
     */
    public function installModule($modulePath)
    {
        $module = $this->loadModule($modulePath);

        $moduleCache = oxNew(\OxidEsales\Eshop\Core\Module\ModuleCache::class, $module);
        $moduleInstaller = oxNew(\OxidEsales\Eshop\Core\Module\ModuleInstaller::class, $moduleCache);
        if (!$moduleInstaller->activate($module)) {
            throw new Exception("Error on module installation: " . $module->getId());
        }
    }

    /**
     * Prepares modules for activation. Registers all modules that exist in the shop.
     *
     * @param string $moduleDirectory The base directory of modules.
     */
    private function prepareModulesForActivation($moduleDirectory)
    {
        $moduleList = oxNew(\OxidEsales\Eshop\Core\Module\ModuleList::class);
        $moduleList->getModulesFromDir($moduleDirectory);
    }

    /**
     * Loads module object from given directory.
     *
     * @param string $modulePath The path to the module.
     *
     * @return \OxidEsales\Eshop\Core\Module\Module
     * @throws Exception
     */
    private function loadModule($modulePath)
    {
        $module = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
        if (!$module->loadByDir($modulePath)) {
            throw new Exception("Module not found");
        }
        return $module;
    }
}
