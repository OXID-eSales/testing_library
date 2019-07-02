<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Library;

use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelperInterface;
use OxidEsales\TestingLibrary\Services\Library\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
class ProjectConfigurationHandler
{
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
        if (!file_exists($this->getOriginalConfigurationPath())) {
            throw new FileNotFoundException('Unable to backup ' . $this->getOriginalConfigurationPath() . '. It does not exist.');
        }
        $this->recursiveCopy($this->getOriginalConfigurationPath(), $this->getBackupConfigurationPath());
    }

    /**
     * Restore project configuration.
     * @throws FileNotFoundException
     */
    public function restore()
    {
        if (!file_exists($this->getBackupConfigurationPath())) {
            throw new FileNotFoundException('Unable to restore ' . $this->getBackupConfigurationPath() . '. It does not exist.');
        }
        $this->rmdirRecursive($this->getOriginalConfigurationPath());
        $this->recursiveCopy($this->getBackupConfigurationPath(), $this->getOriginalConfigurationPath());
    }

    /**
     * Deletes project configuration backup file.
     * @throws FileNotFoundException
     */
    public function cleanup()
    {
        if (!file_exists($this->getBackupConfigurationPath())) {
            throw new FileNotFoundException('Unable to delete ' . $this->getBackupConfigurationPath() . '. It does not exist.');
        }
        $this->rmdirRecursive($this->getBackupConfigurationPath());
    }

    /**
     * @return string
     */
    private function getOriginalConfigurationPath(): string
    {
        return Path::join($this->configurationHelper->getConfigurationDirectoryPath());
    }

    /**
     * @return string
     */
    private function getBackupConfigurationPath(): string
    {
        return Path::join($this->configurationHelper->getConfigurationDirectoryPath() . '-backup');
    }

    /**
     * @param string $source
     * @param string $destination
     */
    private function recursiveCopy(string $source, string $destination) : void
    {
        $filesystem = new Filesystem();
        $filesystem->mirror($source, $destination);
    }

    /**
     * @param string $directory
     */
    private function rmdirRecursive(string $directory): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($directory);
    }
}
