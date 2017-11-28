<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
