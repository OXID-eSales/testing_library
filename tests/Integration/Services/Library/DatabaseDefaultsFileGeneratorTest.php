<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\TestingLibrary\Services\Library\DatabaseDefaultsFileGenerator;

class DatabaseDefaultsFileGeneratorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testFileGeneration()
    {
        $user = 'testUser';
        $password = 'testPassword';
        $host = 'testHost';
        $port = '1111';

        $configFile = $this->getMockBuilder('OxidEsales\Eshop\Core\ConfigFile')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $configFile->dbUser = $user;
        $configFile->dbPwd = $password;
        $configFile->dbHost = $host;
        $configFile->dbPort = $port;
        $generator = new DatabaseDefaultsFileGenerator($configFile);
        $file = $generator->generate();
        $fileContents = file_get_contents($file);

        $this->assertTrue((bool)strpos($fileContents, $user));
        $this->assertTrue((bool)strpos($fileContents, $password));
        $this->assertTrue((bool)strpos($fileContents, $host));
        $this->assertTrue((bool)strpos($fileContents, $port));

        unlink($file);
    }
}
