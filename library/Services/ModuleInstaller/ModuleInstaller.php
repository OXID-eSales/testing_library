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
     * @return null
     */
    public function init($request)
    {
        $modulesToActivate = $request->getParameter("modulestoactivate");
        $moduleDirectory = oxRegistry::getConfig()->getModulesDir();

        $this->prepareModulesForActivation($moduleDirectory);
        foreach ($modulesToActivate as $modulePath) {
            $this->installModule($modulePath);
        }
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

        $moduleCache = oxNew('oxModuleCache', $module);
        $moduleInstaller = oxNew('oxModuleInstaller', $moduleCache);
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
        $moduleList = oxNew("oxModuleList");
        $moduleList->getModulesFromDir($moduleDirectory);
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
        $module = oxNew('oxModule');
        if (!$module->loadByDir($modulePath)) {
            throw new Exception("Module not found");
        }
        return $module;
    }
}
