<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Library;

use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelper;

/**
 * @internal
 */
class ProjectConfigurationHandler
{
    /**
     * @var string project configuration path
     */
    private $projectConfigurationDirectoryPath;

    private $projectConfigurationOriginalFile = "project_configuration.yml";

    private $projectConfigurationBackupFile = "project_configuration.yml.back";


    public function __construct()
    {
        $this->projectConfigurationDirectoryPath = (new ProjectConfigurationHelper())->getConfigurationDirectoryPath();
    }

    /**
     * Backup project configuration.
     */
    public function backup()
    {
        if (file_exists($this->getOriginalFilePath())) {
            file_put_contents($this->getBackupFilePath(), file_get_contents($this->getOriginalFilePath()));
        }
    }

    /**
     * Restore the configuration.
     */
    public function restore()
    {
        if (file_exists($this->getBackupFilePath())) {
            file_put_contents($this->getOriginalFilePath(), file_get_contents($this->getBackupFilePath()));
            unlink($this->getBackupFilePath());
        }
    }

    /**
     * @return Project Configuration original file path string
     */
    private function getOriginalFilePath(): string
    {
        return $this->projectConfigurationDirectoryPath . $this->projectConfigurationOriginalFile;
    }

    /**
     * @return Project Configuration backup file path string
     */
    private function getBackupFilePath(): string
    {
        return $this->projectConfigurationDirectoryPath . $this->projectConfigurationBackupFile;
    }
}
