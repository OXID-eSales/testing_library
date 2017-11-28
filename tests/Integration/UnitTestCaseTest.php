<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Integration;

use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * Empty non abstract class for testing the unit test case.
 */
class testCase extends \OxidEsales\TestingLibrary\UnitTestCase
{

}

/**
 * Integration test for the UnitTestCase class.
 */
class UnitTestCaseTest extends UnitTestCase
{

    /**
     * Test, that the method 'getSessionParam' works as expected.
     *
     * @return testCase The object we tested right now.
     */
    public function testGetSessionParamWorks()
    {
        $unitTestCase = new testCase();

        $sessionKeyIsAdmin = 'blIsAdmin';

        $this->assertFalse($unitTestCase->getSessionParam($sessionKeyIsAdmin));
        $this->assertSessionValueIsCorrect($unitTestCase, $sessionKeyIsAdmin);

        return $unitTestCase;
    }

    /**
     * Test, that the method 'setSessionParam' works as expected.
     */
    public function testSetSessionParamWorks()
    {
        $unitTestCase = $this->testGetSessionParamWorks();

        $sessionKeyIsAdmin = 'blIsAdmin';
        $oldValue = $unitTestCase->getSessionParam($sessionKeyIsAdmin);

        $unitTestCase->setSessionParam($sessionKeyIsAdmin, !$oldValue);

        $expectedSessionParamOne = $unitTestCase->getSessionParam($sessionKeyIsAdmin);

        $unitTestCase->setSessionParam($sessionKeyIsAdmin, $oldValue);

        $expectedSessionParamTwo = $unitTestCase->getSessionParam($sessionKeyIsAdmin);

        $this->assertSame(!$oldValue, $expectedSessionParamOne);
        $this->assertSame($oldValue, $expectedSessionParamTwo);

        $this->assertSessionValueIsCorrect($unitTestCase, $sessionKeyIsAdmin);
    }

    /**
     * Test, that the method 'getRequestParameter' gives back an empty array, when nothing is put in before.
     */
    public function testGetRequestParameterIsEmptyIfNothingIsSetBefore()
    {
        $unitTestCase = new testCase();

        $requestParameter = $unitTestCase->getRequestParameter('');

        $this->assertEmpty($requestParameter);
        $this->assertNull($requestParameter);
    }

    /**
     * Test, that the method 'getRequestParameter' gives back an empty array, when nothing is put in before.
     */
    public function testSetRequestParameterWorks()
    {
        $unitTestCase = new testCase();

        $parameterName = 'XYZ';
        $parameterValue = 'ABC';

        $oldRequestParameter = $unitTestCase->getRequestParameter($parameterName);

        $unitTestCase->setRequestParameter($parameterName, $parameterValue);

        $setRequestParameter = $unitTestCase->getRequestParameter($parameterName);

        $unitTestCase->setRequestParameter($parameterName, null);

        $afterRequestParameter = $unitTestCase->getRequestParameter($parameterName);

        $this->assertEmpty($oldRequestParameter);
        $this->assertNull($oldRequestParameter);

        $this->assertNotEmpty($setRequestParameter);
        $this->assertSame($parameterValue, $setRequestParameter);

        $this->assertEmpty($afterRequestParameter);
        $this->assertNull($afterRequestParameter);
    }

    /**
     * Test, that the method 'getConfigParam' works as expected.
     */
    public function testGetConfigParamGivesBackCorrectValue()
    {
        $unitTestCase = new testCase();

        $this->assertSame('0', $unitTestCase->getConfigParam('sDefaultLang'));
    }

    /**
     * Test, that the method 'setConfigParam' works as expected.
     */
    public function testSetConfigParamSetsValueCorrect()
    {
        $unitTestCase = new testCase();

        $newConfigValue = '12';
        $oldConfigValue = $unitTestCase->getConfigParam('sDefaultLang');

        $unitTestCase->setConfigParam('sDefaultLang', $newConfigValue);

        $setConfigValue = $unitTestCase->getConfigParam('sDefaultLang');

        $unitTestCase->setConfigParam('sDefaultLang', $oldConfigValue);

        $afterConfigValue = $unitTestCase->getConfigParam('sDefaultLang');

        $this->assertSame('0', $oldConfigValue);
        $this->assertSame($newConfigValue, $setConfigValue);
        $this->assertSame($oldConfigValue, $afterConfigValue);
    }

    /**
     * Test, that the method 'setAdminMode' works for the false case.
     */
    public function testSetAdminModeFalseCase()
    {
        $unitTestCase = new testCase();

        $oldAdminMode = $unitTestCase->getConfig()->isAdmin();
        $oldSessionAdminMode = $_SESSION['blIsAdmin'];

        $unitTestCase->setAdminMode(false);

        $setSessionAdminMode = $unitTestCase->getSessionParam('blIsAdmin');
        $setConfigAdminMode = $unitTestCase->getConfig()->isAdmin();

        $unitTestCase->getConfig()->setAdminMode($oldAdminMode);
        $unitTestCase->setSessionParam($oldSessionAdminMode);

        $this->assertFalse($setConfigAdminMode);
        $this->assertFalse($setSessionAdminMode);
    }

    /**
     * Test, that the method 'setAdminMode' works for the true case.
     */
    public function testSetAdminModeTrueCase()
    {
        $unitTestCase = new testCase();

        $oldAdminMode = $unitTestCase->getConfig()->isAdmin();
        $oldSessionAdminMode = $_SESSION['blIsAdmin'];

        $unitTestCase->setAdminMode(true);

        $setSessionAdminMode = $unitTestCase->getSessionParam('blIsAdmin');
        $setConfigAdminMode = $unitTestCase->getConfig()->isAdmin();

        $unitTestCase->getConfig()->setAdminMode($oldAdminMode);
        $unitTestCase->setSessionParam($oldSessionAdminMode);

        $this->assertTrue($setConfigAdminMode);
        $this->assertTrue($setSessionAdminMode);
    }

    /**
     * Ensure, that the session parameter, given by the method 'getSessionParam' is the same as the actual one in the $_SESSION.
     *
     * @param testCase $unitTestCase The object under test, we want to ensure to give us back the correct session value.
     * @param string   $sessionKey   The key of the session value we want to check.
     */
    protected function assertSessionValueIsCorrect($unitTestCase, $sessionKey)
    {
        $this->assertSame($_SESSION[$sessionKey], $unitTestCase->getSessionParam($sessionKey));
    }

}
