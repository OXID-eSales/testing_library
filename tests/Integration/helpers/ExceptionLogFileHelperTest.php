<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Tests\Integration\helpers;

class ExceptionLogFileHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderWrongConstructorParameters
     * @covers       \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::__construct
     *
     * @param $constructorParameters
     */
    public function testConstructorThrowsExpectedExceptionOnWrongParameters($constructorParameters)
    {
        $this->setExpectedException(
            \OxidEsales\Eshop\Core\Exception\StandardException::class,
            'Constructor parameter $exceptionLogFile must be a non empty string'
        );
        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($constructorParameters);
    }

    public function dataProviderWrongConstructorParameters()
    {
        return [
            [''],
            [[]],
            [new \StdClass()],
            [false],
            [true],
            [1],
            [0],
        ];
    }

    /**
     * @covers \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::getExceptionLogFileContent
     */
    public function testGetExceptionLogFileContentThrowsExpectedOnFileNotReadable()
    {
        $exceptionLogFile = './non_existent_file.log';
        $expectedExceptionMessage = 'File ' . $exceptionLogFile . ' could not be read';

        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($exceptionLogFile);

        $actualExceptionMessage = '';
        $exceptionThrown = false;
        try {
            // We do not want the E_WARNING issued by file_get_contrents to break our test
            $originalErrorReportingLevel = error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
            $logFileContent = $exceptionLogFileHelper->getExceptionLogFileContent();
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $actualException) {
            $actualExceptionMessage = $actualException->getMessage();
            $exceptionThrown = true;
        } finally {
            error_reporting($originalErrorReportingLevel);
        }

        $this->assertEquals($expectedExceptionMessage, $actualExceptionMessage);
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @dataProvider dataProviderExpectedContent
     * @covers       \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::getExceptionLogFileContent
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testGetExceptionLogFileContentReturnsExpectedContent($expectedContent)
    {
        $exceptionLogFileRessource = tmpfile();
        $exceptionLogFile = stream_get_meta_data($exceptionLogFileRessource)['uri'];
        fwrite($exceptionLogFileRessource, $expectedContent);

        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($exceptionLogFile);

        $actualContent = $exceptionLogFileHelper->getExceptionLogFileContent();

        fclose($exceptionLogFileRessource);

        $this->assertSame($expectedContent, $actualContent);
    }

    public function dataProviderExpectedContent()
    {
        return [
            [''],
            ['test'],
            ['tèßt'],
            ["
            
            test
            
            "]
        ];
    }

    /**
     * @covers \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::clearExceptionLogFile
     */
    public function testClearExceptionLogFileThrowsExceptionOnFileNotWritable()
    {
        $exceptionLogFileRessource = tmpfile();
        fwrite($exceptionLogFileRessource, 'test');
        $exceptionLogFile = stream_get_meta_data($exceptionLogFileRessource)['uri'];

        $expectedExceptionMessage = 'File ' . $exceptionLogFile . ' could not be opened in write mode';

        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($exceptionLogFile);
        chmod($exceptionLogFile, 0444);
        $this->assertFalse(is_writable($exceptionLogFile));

        $actualExceptionMessage = '';
        $exceptionThrown = false;
        try {
            // We do not want the E_WARNING issued by file_get_contrents to break or test
            $originalErrorReportingLevel = error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
            $exceptionLogFileHelper->clearExceptionLogFile();
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $actualException) {
            $actualExceptionMessage = $actualException->getMessage();
            $exceptionThrown = true;
        } finally {
            error_reporting($originalErrorReportingLevel);
            fclose($exceptionLogFileRessource);
        }

        $this->assertEquals($expectedExceptionMessage, $actualExceptionMessage);
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @covers \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::clearExceptionLogFile
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testClearExceptionLogFileDeletesExceptionLogFileContent()
    {
        $exceptionLogFileRessource = tmpfile();
        fwrite($exceptionLogFileRessource, 'test');
        $exceptionLogFile = stream_get_meta_data($exceptionLogFileRessource)['uri'];

        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($exceptionLogFile);
        $exceptionLogFileHelper->clearExceptionLogFile();

        $actualContent = $exceptionLogFileHelper->getExceptionLogFileContent();

        fclose($exceptionLogFileRessource);

        $this->assertEmpty($actualContent);
    }

    /**
     * @dataProvider dataProviderNumberOfExceptionsToBeLogged
     * @covers       \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper::getParsedExceptions
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testGetParsedExceptionsReturnsExpectedValue($exceptionsToBeLogged)
    {
        $exceptionHandler = new \OxidEsales\EshopCommunity\Core\Exception\ExceptionHandler();

        $expectedLevel = 'exception';
        $expectedType = \OxidEsales\Eshop\Core\Exception\StandardException::class;
        $expectedMessage = 'test message';
        $expectedCode = 1024;
        $expectedFile = __FILE__;
        $expectedLine = __LINE__ + 2;

        $exception = new \OxidEsales\Eshop\Core\Exception\StandardException($expectedMessage, $expectedCode);
        $formattedException = $exceptionHandler->getFormattedException($exception);

        $exceptionLogFileRessource = tmpfile();
        $exceptionLogFile = stream_get_meta_data($exceptionLogFileRessource)['uri'];

        for ($i = 0; $i < $exceptionsToBeLogged; $i++) {
            file_put_contents($exceptionLogFile, $formattedException, FILE_APPEND);
        }

        $exceptionLogFileHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper($exceptionLogFile);
        $actualParsedExceptions = $exceptionLogFileHelper->getParsedExceptions();

        fclose($exceptionLogFileRessource);

        for ($i = 0; $i < $exceptionsToBeLogged; $i++) {
            $this->assertEquals($expectedLevel, $actualParsedExceptions[$i]['level']);
            $this->assertEquals($expectedType, $actualParsedExceptions[$i]['type']);
            $this->assertEquals($expectedCode, $actualParsedExceptions[$i]['code']);
            $this->assertEquals($expectedFile, $actualParsedExceptions[$i]['file']);
            $this->assertEquals($expectedLine, $actualParsedExceptions[$i]['line']);
            $this->assertEquals($expectedMessage, $actualParsedExceptions[$i]['message']);
        }
    }

    public function dataProviderNumberOfExceptionsToBeLogged()
    {
        return [
            [0],
            [1],
            [5],
        ];
    }
}
