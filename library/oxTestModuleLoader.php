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
    /** @var array */
    protected static $moduleData = array('chains' => array(), 'paths' => array());

    /** @var bool Whether to use original chains. */
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
     * @param array $modules Array of modules to load.
     */
    public function loadModules($modules)
    {
        $errors = array();
        $modules = is_array($modules) ? $modules : array($modules);

        $modulesDir = oxRegistry::getConfig()->getModulesDir();
        foreach ($modules as $module) {
            $fullPath = $modulesDir . $module;
            if (file_exists($fullPath . "/metadata.php")) {
                self::$moduleData['paths'][] = $module;
                self::_initMetadata($fullPath . "/metadata.php");
            } else {
                $errors[] = "Unable to find metadata file in directory: $fullPath" . PHP_EOL;
            }
        }

        if ($errors) {
            die(implode("\n\n", $errors));
        }
    }

    /**
     * Calls ModuleInstaller Service and activates all given modules in shop.
     *
     * @param array $modulesToActivate Array of modules to activate.
     */
    public function activateModules($modulesToActivate)
    {
        $serviceCaller = new oxServiceCaller();
        $serviceCaller->setParameter('modulestoactivate', $modulesToActivate);
        $serviceCaller->callService('ModuleInstaller', 1);
    }

    /**
     * Loads the module from metadata file
     * If no metadata found and the module chain is empty, then does nothing.
     *
     * On first load the data is saved and on consecutive calls the saved data is used
     */
    public function setModuleInformation()
    {
        if (count(self::$moduleData['chains'])) {
            $utilsObject = oxRegistry::get("oxUtilsObject");
            $config = oxRegistry::getConfig();

            $utilsObject->setModuleVar("aModules", self::$moduleData['chains']);
            $config->setConfigParam("aModules", self::$moduleData['chains']);
            $utilsObject->setModuleVar("aDisabledModules", array());
            $config->setConfigParam("aDisabledModules", array());
            $utilsObject->setModuleVar("aModulePaths", self::$moduleData['paths']);
            $config->setConfigParam("aModulePaths", self::$moduleData['paths']);

            // Mocking of module classes does not work without calling oxNew first.
            foreach (self::$moduleData['chains'] as $parent => $chain) {
                $utilsObject->getClassName($parent);
            }
        }
    }

    /**
     * Returns modules path.
     *
     * @return string
     */
    protected function _getModulesPath()
    {
        return oxRegistry::getConfig()->getConfigParam("sShopDir") . "/modules/";
    }

    /**
     * Loads the module files and extensions from the given metadata file
     *
     * @param string $sPath path to the metadata file
     */
    private function _initMetadata($sPath)
    {
        include $sPath;

        // including all filles from ["files"]
        if (isset($aModule["files"]) && count($aModule["files"])) {
            $this->_includeModuleFiles($aModule["files"]);
        }

        // adding and extending the module files
        if (isset($aModule["extend"]) && count($aModule["extend"])) {
            $this->_appendToChain($aModule["extend"]);
        }

        // adding settings
        if (isset($aModule["settings"]) && count($aModule["settings"])) {
            $this->_addSettings($aModule["settings"]);
        }

        // running onActivate method.
        if (isset($aModule["events"]) && isset($aModule["events"]["onActivate"])) {
            if (is_callable($aModule["events"]["onActivate"])) {
                call_user_func($aModule["events"]["onActivate"]);
            }
        }
    }

    /**
     * Include module files.
     *
     * @param array $files
     */
    private function _includeModuleFiles($files)
    {
        foreach ($files as $filePath) {
            $className = basename($filePath);
            $className = substr($className, 0, strlen($className) - 4);

            if (!class_exists($className, false)) {
                require oxRegistry::getConfig()->getConfigParam("sShopDir") . "/modules/" . $filePath;
            }
        }
    }

    /**
     * Appends extended files to module chain.
     * Adds to "original" chain if needed.
     * Adding the "extend" chain to the main chain.
     *
     * @param array $extend
     */
    private function _appendToChain($extend)
    {
        if (self::$original && !count(self::$moduleData['chains'])) {
            self::$moduleData['chains'] = (array)oxRegistry::getConfig()->getConfigParam("aModules");
        }

        foreach ($extend as $parent => $extends) {
            if (isset(self::$moduleData['chains'][$parent])) {
                $extends = trim(self::$moduleData['chains'][$parent], "& ") . "&"
                    . trim($extends, "& ");
            }
            self::$moduleData['chains'][$parent] = $extends;
        }
    }

    /**
     * Adds settings to configuration.
     *
     * @param array $settings
     */
    private function _addSettings($settings)
    {
        $config = oxRegistry::getConfig();
        foreach ($settings as $setting) {
            $config->saveShopConfVar($setting['type'], $setting['name'], $setting['value']);
        }
    }
}
