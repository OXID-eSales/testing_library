<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use Exception;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\Facts\Facts;
use OxidEsales\TestingLibrary\Services\Library\Cache;
use RuntimeException;
use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

use function is_file;

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

        $this->makeModuleServicesAvailableInDIContainer();

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
        if ($module->isActive()) {
            return;
        }

        /** Clean all caches before module activation */
        $this->clearShopTmpFolder();

        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getInstance();
        $database->flushTableDescriptionCache();

        $cachedClassInstances = Registry::getKeys();

        $this->activateModule($module->getId());
        Registry::getConfig()->reinitialize();

        foreach ($cachedClassInstances as $cachedClassInstance) {
            if (\OxidEsales\Eshop\Core\ConfigFile::class !== $cachedClassInstance) {
                Registry::set($cachedClassInstance, null);
            }
        }
        $baseClass = new \OxidEsales\Eshop\Core\Base();
        $baseClass->setConfig(null);
        $baseClass->setSession(null);
        $baseClass->setUser(null);
        $baseClass->setAdminMode(null);

        if (method_exists($baseClass, 'setRights')) {
            $baseClass->setRights(null);
        }

        $this->clearShopTmpFolder();
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
            throw new Exception('Module configuration not found for module with path ' . $modulePath);
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

    /**
     * Shop cache should be deleted as some modules might try to clean cache on top.
     * This creates problems if CI and Apache user is different.
     * Some modules might not clean cache which would also lead to a random errors/failures.
     */
    private function clearShopTmpFolder()
    {
        $cache = new Cache();
        $cache->clearTemporaryDirectory();
    }

    private function makeModuleServicesAvailableInDIContainer(): void
    {
        ContainerFactory::resetContainer();
    }

    private function activateModule(string $moduleId): void
    {
        $rootPath =  (new Facts())->getShopRootPath();
        $process = new Process(
            [$this->getConsoleRunner($rootPath), 'oe:module:activate', $moduleId],
            $rootPath
        );
        $process->mustRun();
    }

    public function getConsoleRunner(string $rootPath): string
    {
        $possiblePaths = [
            'bin/oe-console',
            'vendor/bin/oe-console',
        ];
        foreach ($possiblePaths as $path) {
            if (is_file(Path::join($rootPath, $path))) {
                return $path;
            }
        }
        throw new RuntimeException('Could not find script "bin/oe-console" to activate module');
    }
}
