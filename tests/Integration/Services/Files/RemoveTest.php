<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Tests\Integration\Services\Files;

use OxidEsales\TestingLibrary\Services\Files\Remove;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;

class RemoveTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testRemoveWhenNoFilesProvided()
    {
        $rootPath = FilesHelper::prepareStructureAndReturnPath($this->getDirectoryStructure());
        $this->initializeFilesRemoval($rootPath, []);
        $this->assertTrue(file_exists($rootPath.'/testDirectory/someFile.php'), "$rootPath/testDirectory/someFile.php");
        $this->assertTrue(file_exists($rootPath.'/testDirectory/someFile2.php'), "$rootPath/testDirectory/someFile2.php");
    }

    public function testRemoveFile()
    {
        $rootPath = FilesHelper::prepareStructureAndReturnPath($this->getDirectoryStructure());
        $this->initializeFilesRemoval($rootPath, [$rootPath.'/testDirectory/someFile.php']);
        $this->assertFalse(file_exists($rootPath.'/testDirectory/someFile.php'), "$rootPath/testDirectory/someFile.php");
        $this->assertTrue(file_exists($rootPath.'/testDirectory/someFile2.php'), "$rootPath/testDirectory/someFile2.php");
    }

    /**
     * @param string $rootPath
     * @param array $files
     */
    protected function initializeFilesRemoval($rootPath, $files)
    {
        $removeService = new Remove(new ServiceConfig($rootPath));
        $request = new Request([Remove::FILES_PARAMETER_NAME => $files]);
        $removeService->init($request);
    }

    /**
     * Get directory structure to mock for the tests.
     *
     * @return array
     */
    private function getDirectoryStructure()
    {
        return [
            'testDirectory' => [
                'someFile.php' => 'content',
                'someFile2.php' => 'content',
            ]
        ];
    }
}
