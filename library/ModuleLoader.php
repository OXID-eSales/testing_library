<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
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
        foreach ($modulesToActivate as $moduleId) {
            $this->activateModule($moduleId);
        }

        $this->makeModuleServicesAvailableInDIContainer();
    }

    private function activateModule(string $moduleId): void
    {
        $moduleActivationService = $this->getContainer()->get(ModuleActivationBridgeInterface::class);

        if (!$moduleActivationService->isActive($moduleId, Registry::getConfig()->getShopId())) {
            $moduleActivationService->activate($moduleId, Registry::getConfig()->getShopId());
        }
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
