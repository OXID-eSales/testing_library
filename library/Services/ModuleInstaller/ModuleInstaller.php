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
        $moduleDirectory   = $request->getParameter("moduledirectory");
        $modulesToActivate = $request->getParameter("modulestoactivate");

        $this->prepareModulesForActivation($moduleDirectory);
        foreach ($modulesToActivate as $sModulePath) {
            $this->installModule($sModulePath);
        }
    }

    /**
     * Activates module.
     *
     * @param string $sModulePath The path to the module.
     *
     * @throws Exception
     */
    public function installModule($sModulePath)
    {
        $oModule = $this->loadModule($sModulePath);

        /** @var oxModuleCache $oModuleCache */
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        /** @var oxModuleInstaller $oModuleInstaller */
        $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);
        if (!$oModuleInstaller->activate($oModule)) {
            throw new Exception("Error on module installation: " . $oModule->getId());
        }
    }

    /**
     * Prepares modules for activation. Registers all modules that exist in the shop.
     *
     * @param string $moduleDirectory The base directory of modules.
     */
    private function prepareModulesForActivation($moduleDirectory)
    {
        $oModuleList = oxNew("oxModuleList");
        $oModuleList->getModulesFromDir($moduleDirectory);
    }

    /**
     * Loads module object from given directory.
     *
     * @param string $sModulePath The path to the module.
     *
     * @return oxModule
     * @throws Exception
     */
    private function loadModule($sModulePath)
    {
        /** @var oxModule $oModule */
        $oModule = oxNew('oxModule');
        if (!$oModule->loadByDir($sModulePath)) {
            throw new Exception("Module not found");
        }
        return $oModule;
    }
}
