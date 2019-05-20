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
    public function testFileBackup()
    {
        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();

        $this->assertFileExists(Path::join($projectConfigurationDirectoryPath, ProjectConfigurationHandler::PROJECT_CONFIGURATION_BACKUP_FILE_NAME));
    }

    public function testFileBackupWhenItDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        unlink(Path::join($projectConfigurationDirectoryPath, ProjectConfigurationHandler::PROJECT_CONFIGURATION_FILE_NAME));
        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();
    }

    public function testFileRestoration()
    {
        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();
        unlink(Path::join($projectConfigurationDirectoryPath, ProjectConfigurationHandler::PROJECT_CONFIGURATION_FILE_NAME));
        $handler->restore();

        $this->assertFileExists(Path::join($projectConfigurationDirectoryPath, ProjectConfigurationHandler::PROJECT_CONFIGURATION_FILE_NAME));
    }

    public function testFileRestorationWhenItDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->restore();
    }

    public function testFileCleanup()
    {
        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->backup();
        $handler->cleanup();

        $this->assertFileNotExists(Path::join($projectConfigurationDirectoryPath, ProjectConfigurationHandler::PROJECT_CONFIGURATION_BACKUP_FILE_NAME));
    }

    public function testFileCleanupWhenFileDoesNotExists()
    {
        $this->expectException(FileNotFoundException::class);

        $projectConfigurationDirectoryPath = $this->makeVirtualProjectConfigurationDirectory();
        $projectConfigurationHelperStub = $this->makeProjectConfigurationHelperStub($projectConfigurationDirectoryPath);

        $handler = new ProjectConfigurationHandler($projectConfigurationHelperStub);
        $handler->cleanup();
    }

    /**
     * @return string
     */
    private function makeVirtualProjectConfigurationDirectory(): string
    {
        $vfsDirectoryObject = vfsStream::setup();
        vfsStream::newFile(ProjectConfigurationHandler::PROJECT_CONFIGURATION_FILE_NAME)->at($vfsDirectoryObject)->setContent("anything");
        return $vfsDirectoryObject->url();
    }

    /**
     * @param string $projectConfigurationDirectoryPath
     * @return ProjectConfigurationHelperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function makeProjectConfigurationHelperStub(string $projectConfigurationDirectoryPath)
    {
        $projectConfigurationHelperStub = $this->getMockBuilder(ProjectConfigurationHelperInterface::class)
            ->getMock();
        $projectConfigurationHelperStub->method('getConfigurationDirectoryPath')->willReturn($projectConfigurationDirectoryPath);
        return $projectConfigurationHelperStub;
    }
}
