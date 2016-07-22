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

/**
 * Class oxTestModuleLoader
 */
class oxTestModuleLoader
{
    /**
     * @var bool Whether to use original chains.
     */
    protected static $original = false;

    /**
     * Sets the original chain loading command
     *
     * @param boolean $original
     */
    public function useOriginalChain($original)
    {
        self::$original = $original;
    }

    /**
     * Tries to initiate the module classes and includes required files from metadata
     *
     * @deprecated since v1.0.2 (2016-07-22). Please use the method oxTestModuleLoader::activateModules();
     *
     * @param array $modulesToActivate Array of modules to load.
     */
    public function loadModules($modulesToActivate)
    {
        $this->activateModules($modulesToActivate);
    }

    /**
     * Loads the module from metadata file
     * If no metadata found and the module chain is empty, then does nothing.
     *
     * On first load the data is saved and on consecutive calls the saved data is used
     *
     * @deprecated since v1.0.2 (2016-07-22). Will be removed, for modules activation please use activateModules();
     */
    public function setModuleInformation()
    {
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
    }

    /**
     * Prepares modules for activation. Registers all modules that exist in the shop.
     */
    private function prepareModulesForActivation()
    {
        $moduleDirectory = oxRegistry::getConfig()->getModulesDir();
        $moduleList = new oxModuleList();
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

        $moduleCache = new oxModuleCache($module);
        $moduleInstaller = new oxModuleInstaller($moduleCache);
        if (!$moduleInstaller->activate($module)) {
            throw new Exception("Error on module installation: " . $module->getId());
        }
    }

    /**
     * Loads module object from given directory.
     *
     * @param string $modulePath The path to the module.
     *
     * @return oxModule
     * @throws Exception
     */
    private function loadModule($modulePath)
    {
        $module = new oxModule();
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
        if (!self::$original) {
            oxRegistry::getConfig()->setConfigParam("aModules", '');
        }
    }
}
