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

use OxidEsales\TestingLibrary\Services\Files\ChangeRights;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use PHPUnit_Framework_TestCase;

class ChangeRightsTest extends PHPUnit_Framework_TestCase
{
    public function testChangeRightsForOneFile()
    {
        $rootPath = FilesHelper::prepareStructureAndReturnPath();
        $this->initializeFileRightsChange($rootPath, ['/testDirectory/someFile.php'], '111');
        $this->assertFalse(is_writable($rootPath.'/testDirectory/someFile.php'), "$rootPath/testDirectory/someFile.php");
        $this->assertTrue(is_writable($rootPath.'/testDirectory/someFile2.php'), "$rootPath/testDirectory/someFile2.php");
    }

    public function testChangeRightsWhenFileDoesNotExistDoesNotThrowException()
    {
        $rootPath = FilesHelper::prepareStructureAndReturnPath();
        $this->initializeFileRightsChange($rootPath, ['/testDirectory/someNotExistingFile.php'], '111');
    }

    /**
     * @param string $rootPath
     * @param array $files
     */
    protected function initializeFileRightsChange($rootPath, $files, $rights)
    {
        $changeRightsService = new ChangeRights(new ServiceConfig($rootPath));
        $request = new Request(
            [
                ChangeRights::FILES_PARAMETER_PATH => $rootPath,
                ChangeRights::FILES_PARAMETER_NAME => $files,
                ChangeRights::FILES_PARAMETER_RIGHTS => $rights
            ]
        );
        $changeRightsService->init($request);
    }
}
