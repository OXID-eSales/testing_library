<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\TestingLibrary;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Path;

class ModuleLoader
{
    /**
     * @param array $modulesToActivate Array of modules to load.
     */
    public function activateModules(array $modulesToActivate): void
    {
        $moduleActivationService = $this->getContainer()->get(ModuleActivationBridgeInterface::class);
        $shopId = Registry::getConfig()->getShopId();
        foreach ($modulesToActivate as $moduleId) {
            if (!$moduleActivationService->isActive($moduleId, $shopId)) {
                $this->activateModule($moduleId);
                Registry::getConfig()->reinitialize();
            }
        }
        $this->makeModuleServicesAvailableInDIContainer();
    }

    private function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
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

    private function getConsoleRunner(string $rootPath): string
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
