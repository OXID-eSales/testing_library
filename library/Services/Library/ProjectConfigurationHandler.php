<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Library;

use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelperInterface;
use OxidEsales\TestingLibrary\Services\Library\Exception\FileNotFoundException;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
class ProjectConfigurationHandler
{
    const PROJECT_CONFIGURATION_FILE_NAME = 'project_configuration.yml';

    const PROJECT_CONFIGURATION_BACKUP_FILE_NAME = 'project_configuration.yml.bak';

    /**
     * @var ProjectConfigurationHelperInterface
     */
    private $configurationHelper;

    public function __construct(ProjectConfigurationHelperInterface $configurationHelper)
    {
        $this->configurationHelper = $configurationHelper;
    }

    /**
     * Backup project configuration.
     * @throws FileNotFoundException
     */
    public function backup()
    {
        if (!file_exists($this->getOriginalFilePath())) {
            throw new FileNotFoundException('Unable to backup ' . $this->getOriginalFilePath() . 'file. It does not exist.');
        }
        copy($this->getOriginalFilePath(), $this->getBackupFilePath());
    }

    /**
     * Restore project configuration.
     * @throws FileNotFoundException
     */
    public function restore()
    {
        if (!file_exists($this->getBackupFilePath())) {
            throw new FileNotFoundException('Unable to restore ' . $this->getBackupFilePath() . 'file. It does not exist.');
        }
        copy($this->getBackupFilePath(), $this->getOriginalFilePath());
    }

    /**
     * Deletes project configuration backup file.
     * @throws FileNotFoundException
     */
    public function cleanup()
    {
        if (!file_exists($this->getBackupFilePath())) {
            throw new FileNotFoundException('Unable to delete ' . $this->getBackupFilePath() . 'file. It does not exist.');
        }
        unlink($this->getBackupFilePath());
    }

    /**
     * @return string
     */
    private function getOriginalFilePath(): string
    {
        return Path::join($this->configurationHelper->getConfigurationDirectoryPath(), static::PROJECT_CONFIGURATION_FILE_NAME);
    }

    /**
     * @return string
     */
    private function getBackupFilePath(): string
    {
        return Path::join($this->configurationHelper->getConfigurationDirectoryPath(), static::PROJECT_CONFIGURATION_BACKUP_FILE_NAME);
    }
}
