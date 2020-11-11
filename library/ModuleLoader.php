<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Exception\ModuleSetupException;
use Psr\Container\ContainerInterface;

/**
 * Module loader class. Can imitate loaded module for testing.
 */
class ModuleLoader
{
    /**
     * @param array $modulesToActivate Array of modules to load.
     */
    public function activateModules(array $modulesToActivate): void
    {
        foreach ($modulesToActivate as $modulePath) {
            $this->activateModule($modulePath);
        }

        $this->makeModuleServicesAvailableInDIContainer();
    }

    private function activateModule(string $path): void
    {
        $moduleActivationService = $this->getContainer()->get(ModuleActivationBridgeInterface::class);

        $moduleId = $this->getModuleConfiguration($path)->getId();

        if (!$moduleActivationService->isActive($moduleId, Registry::getConfig()->getShopId())) {
            $moduleActivationService->activate($moduleId, Registry::getConfig()->getShopId());
        }
    }

    private function getModuleConfiguration(string $path): ModuleConfiguration
    {
        $moduleConfigurations = $this->getContainer()
            ->get(ShopConfigurationDaoBridgeInterface::class)
            ->get()
            ->getModuleConfigurations();

        foreach ($moduleConfigurations as $moduleConfiguration) {
            if ($moduleConfiguration->getPath() === $path) {
                return $moduleConfiguration;
            }
        }

        throw new Exception('Module with path "' . $path . '" not found in shop module configuration.');
    }

    private function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }

    private function makeModuleServicesAvailableInDIContainer(): void
    {
        ContainerFactory::resetContainer();
    }
}
