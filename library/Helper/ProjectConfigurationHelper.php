<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Helper;

use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\Facts\Facts;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
class ProjectConfigurationHelper
{
    /**
     * @var Facts
     */
    private $facts;

    /**
     * @return Facts
     */
    private function getFacts(): Facts
    {
        if ($this->facts === null) {
            $this->facts = new Facts();
        }

        return $this->facts;
    }

    /**
     * @return string
     */
    public function getConfigurationDirectoryPath(): string
    {
        return $this->getShopRootPath() . '/var/configuration/';
    }

    /**
     * @return string
     */
    public function getProjectConfigurationFilePath(): string
    {
        return $this->getShopRootPath() . '/var/configuration/project_configuration.yml';
    }

    /**
     * @return string
     */
    private function getShopRootPath(): string
    {
        return $this->getFacts()->getShopRootPath();
    }

    /**
     * @return string
     */
    public function getProjectConfigurationData(): string
    {
        if (file_exists($this->getConfigurationDirectoryPath() . "project_configuration.yml")) {
            return file_get_contents($this->getConfigurationDirectoryPath() . "project_configuration.yml");
        }

        return "";
    }
}
