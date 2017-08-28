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

use OxidEsales\TestingLibrary\Services\Files\ChangeExceptionLogRights;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use PHPUnit_Framework_TestCase;

class ChangeExceptionLogRightsTest extends PHPUnit_Framework_TestCase
{
    public function testLogIsWritableForAllUsersWhenFileExist()
    {
        $this->markTestSkipped('Not running at the moment - skipped while investigating.');

        $rootPath = FilesHelper::prepareStructureAndReturnPath(['log' => ['EXCEPTION_LOG.txt' => 'content']]);
        $pathToExceptionLog = "$rootPath/log/EXCEPTION_LOG.txt";
        chmod($pathToExceptionLog, 0111);

        $this->assertFalse(is_writable($pathToExceptionLog));

        $changeRightsService = new ChangeExceptionLogRights(new ServiceConfig($rootPath));
        $changeRightsService->init($request = new Request());

        $filePermissions = $this->getFilePermissions($pathToExceptionLog);
        $this->assertSame('0777', $filePermissions, 'Exception log should be writable.');
    }

    public function testCreateWhenFileDoesNotExist()
    {
        $this->markTestSkipped('Not running at the moment - skipped while investigating.');

        $rootPath = FilesHelper::prepareStructureAndReturnPath(['log' => []]);
        $pathToExceptionLog = "$rootPath/log/EXCEPTION_LOG.txt";

        $this->assertFalse(file_exists($pathToExceptionLog));

        $changeRightsService = new ChangeExceptionLogRights(new ServiceConfig($rootPath));
        $changeRightsService->init($request = new Request());

        $this->assertTrue(file_exists($pathToExceptionLog));
        $filePermissions = $this->getFilePermissions($pathToExceptionLog);
        $this->assertSame('0777', $filePermissions, 'Exception log should be writable.');
    }

    /**
     * Return file permissions in a normal form.
     *
     * @param string $pathToFile
     *
     * @return string
     */
    private function getFilePermissions($pathToFile)
    {
        return substr(sprintf('%o', fileperms($pathToFile)), -4);
    }
}
