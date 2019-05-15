<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Integration\Services\ProjectConfiguration;


use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelper;
use OxidEsales\TestingLibrary\ServiceCaller;
use OxidEsales\TestingLibrary\Services\Library\ProjectConfigurationHandler;
use OxidEsales\TestingLibrary\Services\ProjectConfiguration\ProjectConfiguration;
use OxidEsales\TestingLibrary\TestConfig;
use OxidEsales\TestingLibrary\UnitTestCase;

class ProjectConfigurationTest extends UnitTestCase
{
    public function testProjectConfigurationBackupRestore()
    {
        file_put_contents($this->getProjectConfigurationFilePath(), "this is a test");
        $projectConfiguration = $this->getProjectConfigurations();

        (new ProjectConfigurationHandler())->backup();
        $this->assertEquals($this->getProjectConfigurations(), $projectConfiguration);

        file_put_contents($this->getProjectConfigurationFilePath(), "restore data");
        $this->assertEquals($this->getProjectConfigurations(), "restore data");

        (new ProjectConfigurationHandler())->restore();
        $this->assertEquals($this->getProjectConfigurations(), $projectConfiguration);
    }

    /**
     * @return string
     */
    private function getProjectConfigurations(): string
    {
        return (new ProjectConfigurationHelper())->getProjectConfigurationData();
    }

    /**
     * @return string
     */
    private function getProjectConfigurationFilePath(): string
    {
        return (new ProjectConfigurationHelper())->getProjectConfigurationFilePath();
    }
}
