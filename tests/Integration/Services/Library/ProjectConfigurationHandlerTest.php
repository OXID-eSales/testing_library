<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use org\bovigo\vfs\vfsStream;
use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelperInterface;
use OxidEsales\TestingLibrary\Services\Library\Exception\FileNotFoundException;
use OxidEsales\TestingLibrary\Services\Library\ProjectConfigurationHandler;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

class ProjectConfigurationHandlerTest extends TestCase
{
    private $configurationDirectory;
    private $configurationFileInSubDirectory;

    protected function setUp()
    {
        parent::setUp();
        $this->prepareVfsStructure();
    }

    public function testFileBackup()
    {
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();

        $this->assertFileExists($this->getBackupConfigurationFile());
    }

    public function testFolderBackupWithoutFile()
    {
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        unlink($this->configurationFileInSubDirectory);
        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();

        $this->assertDirectoryExists($this->getConfigurationBackupDirectory());
    }

    public function testFileRestoration()
    {
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();
        unlink($this->configurationFileInSubDirectory);
        $handler->restore();

        $this->assertFileExists($this->getBackupConfigurationFile());
    }

    public function testFolderRestorationWhenItDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->restore();
    }

    public function testFolderCleanup()
    {
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();
        $handler->cleanup();

        $this->assertFileNotExists($this->getBackupConfigurationFile());
    }

    public function testFolderCleanupWhenFileDoesNotExists()
    {
        $this->expectException(FileNotFoundException::class);

        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub();

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->cleanup();
    }

    private function prepareVfsStructure()
    {
        $structure = [
            'configuration' => [
                'shops' => [
                    'configuration.yml' => 'anything',
                ]
            ],
        ];

        $root = vfsStream::setup('root', null, $structure);

        $this->configurationDirectory = vfsStream::url('root/configuration');
        $this->configurationFileInSubDirectory = vfsStream::url(
            'root/configuration/shops/configuration.yml'
        );
    }

    private function getBackupConfigurationFile()
    {
        return vfsStream::url(
            'root/configuration-backup/shops/configuration.yml'
        );
    }

    private function getConfigurationBackupDirectory()
    {
        return vfsStream::url(
            'root/configuration-backup'
        );
    }

    private function makeProjectConfigurationHelperStub(): ProjectConfigurationHelperInterface
    {
        $projectConfigurationHelperStub = $this->getMockBuilder(ProjectConfigurationHelperInterface::class)
            ->getMock();

        $projectConfigurationHelperStub
            ->method('getConfigurationDirectoryPath')
            ->willReturn($this->configurationDirectory);

        return $projectConfigurationHelperStub;
    }
}
