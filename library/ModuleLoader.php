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

namespace OxidEsales\TestingLibrary;

use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleInstaller;
use OxidEsales\Eshop\Core\Module\Module;
use Exception;

/**
 * Module loader class. Can imitate loaded module for testing.
 */
class ModuleLoader
{
    /**
     * @var bool Whether to use original chains.
     */
    protected static $useOriginalChains = false;

    /**
     * Sets the original chain loading command
     *
     * @param boolean $original
     */
    public function useOriginalChain($original)
    {
        self::$useOriginalChains = $original;
    }

    /**
     * Loads modules and activates them.
     *
     * @param array $modulesToActivate Array of modules to load.
     */
    public function activateModules($modulesToActivate)
    {
        $this->clearModuleChain();

        // First load all needed config options before the module will be installed.
        $this->prepareModulesForActivation();
        foreach ($modulesToActivate as $modulePath) {
            $this->installModule($modulePath);
        }

        // Reset reverse proxy backend as module activation sets it to flush mode.
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Cache\ReverseProxy\ReverseProxyBackend::class, null);
    }

    /**
     * Prepares modules for activation. Registers all modules that exist in the shop.
     */
    private function prepareModulesForActivation()
    {
        $moduleDirectory = \OxidEsales\Eshop\Core\Registry::getConfig()->getModulesDir();
        $moduleList = new ModuleList();
        $moduleList->getModulesFromDir($moduleDirectory);
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

        $moduleCache = new ModuleCache($module);
        $moduleInstaller = new ModuleInstaller($moduleCache);
        if (!$moduleInstaller->activate($module)) {
            throw new Exception("Error on module installation: " . $module->getId());
        }
    }

    /**
     * Loads module object from given directory.
     *
     * @param string $modulePath The path to the module.
     *
     * @return Module
     * @throws Exception
     */
    private function loadModule($modulePath)
    {
        $module = new Module();
        if (!$module->loadByDir($modulePath)) {
            throw new Exception("Module not found");
        }
        return $module;
    }

    /**
     * Checks if extended files have to be added to "original" module chain or to empty chain.
     */
    private function clearModuleChain()
    {
        if (!self::$useOriginalChains) {
            \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam("aModules", '');
        }
    }
}
