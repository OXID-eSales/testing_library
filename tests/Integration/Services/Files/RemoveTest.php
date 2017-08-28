<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 */

namespace OxidEsales\TestingLibrary\Tests\Integration\Services\Files;

use OxidEsales\TestingLibrary\Services\Files\Remove;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use PHPUnit_Framework_TestCase;

class RemoveTest extends PHPUnit_Framework_TestCase
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
