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

use OxidEsales\Eshop\Core\Registry;

/**
 * Module loader class. Can imitate loaded moudle for testing.
 */
class ModuleLoader
{
    /** @var array */
    protected static $moduleData = array(
        'chains' => array(),
        'paths' => array(),
        'files' => array(),
        'classes' => array()
    );

    /** @var bool Whether to use original chains. */
    protected static $useOriginalChains = false;

    /**
     * Register autoloader for module files.
     */
    public function __construct()
    {
        spl_autoload_register(function($class) {
            $class = strtolower($class);
            if (array_key_exists($class, self::$moduleData['classes'])) {
                require_once self::$moduleData['classes'][$class];
            }
        });
    }

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
     * Tries to initiate the module classes and include required files from metadata
     *
     * @param array $modules Array of modules to load.
     */
    public function loadModules($modules)
    {
        $modules = is_array($modules) ? $modules : array($modules);

        $modulesDir = Registry::getConfig()->getModulesDir();
        foreach ($modules as $module) {
            $fullPath = $modulesDir . $module;
            if (file_exists($fullPath . "/metadata.php")) {
                self::_initMetadata($module, $fullPath . "/metadata.php");
            } else {
                die("Unable to find metadata file in directory: $fullPath" . PHP_EOL);
            }
        }
    }

    /**
     * Calls ModuleInstaller Service and activates all given modules in shop.
     *
     * @param array $modulesToActivate Array of modules to activate.
     */
    public function activateModules($modulesToActivate)
    {
        $serviceCaller = new ServiceCaller();
        $serviceCaller->setParameter('modulestoactivate', $modulesToActivate);
        $serviceCaller->callService('ModuleInstaller', 1);
    }

    /**
     * Resets information about activated modules.
     */
    public function setModuleInformation()
    {
        $utilsObject = Registry::get("oxUtilsObject");
        $config = Registry::getConfig();

        $utilsObject->setModuleVar("aDisabledModules", array());
        $config->setConfigParam("aDisabledModules", array());

        $utilsObject->setModuleVar("aModulePaths", (array) self::$moduleData['paths']);
        $config->setConfigParam("aModulePaths", (array) self::$moduleData['paths']);
        $utilsObject->setModuleVar("aModuleFiles", (array) self::$moduleData['files']);
        $config->setConfigParam("aModuleFiles", (array) self::$moduleData['files']);
        $utilsObject->setModuleVar("aModules", (array) self::$moduleData['chains']);
        $config->setConfigParam("aModules", (array) self::$moduleData['chains']);

        if (!empty(self::$moduleData['chains'])) {
            // getClassName creates aliases to extended classes. This fixes mocking.
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
        return Registry::getConfig()->getConfigParam("sShopDir") . "/modules/";
    }

    /**
     * Loads the module files and extensions from the given metadata file
     *
     * @param string $metadataPath path to the metadata file
     */
    private function _initMetadata($module, $metadataPath)
    {
        include $metadataPath;

        if (isset($aModule["id"])) {
            self::$moduleData['paths'][$aModule["id"]] = $module;
        }

        // including all files from ["files"]
        if (isset($aModule["files"]) && count($aModule["files"])) {
            $this->registerModuleFiles($aModule["id"], $aModule["files"]);
        }

        // adding and extending the module files
        if (isset($aModule["extend"]) && count($aModule["extend"])) {
            $this->appendToChain($aModule["extend"]);
        }

        // adding settings
        if (isset($aModule["settings"]) && count($aModule["settings"])) {
            $this->addSettings($aModule["settings"]);
        }

        // running onActivate method.
        if (isset($aModule["events"]["onActivate"]) && is_callable($aModule["events"]["onActivate"])) {
            call_user_func($aModule["events"]["onActivate"]);
        }
    }

    /**
     * Registers module files for autoload.
     *
     * @param string $moduleId
     * @param array  $files
     */
    private function registerModuleFiles($moduleId, $files)
    {
        self::$moduleData['files'][$moduleId] = array_change_key_case($files, CASE_LOWER);

        $modulesDirectory = Registry::getConfig()->getConfigParam("sShopDir") ."/modules/";
        foreach ($files as $filePath) {
            $class = strtolower(substr(basename($filePath), 0, -4));
            self::$moduleData['classes'][$class] = $modulesDirectory . $filePath;
        }
    }

    /**
     * Appends extended files to module chain.
     * Adds to "original" chain if needed.
     * Adding the "extend" chain to the main chain.
     *
     * @param array $extend
     */
    private function appendToChain($extend)
    {
        if (self::$useOriginalChains && !count(self::$moduleData['chains'])) {
            self::$moduleData['chains'] = (array) Registry::getConfig()->getConfigParam("aModules");
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
    private function addSettings($settings)
    {
        $config = Registry::getConfig();
        foreach ($settings as $setting) {
            $config->saveShopConfVar($setting['type'], $setting['name'], $setting['value']);
        }
    }
}
