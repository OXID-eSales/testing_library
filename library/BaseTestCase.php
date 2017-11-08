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
 * @copyright (C) OXID eSales AG 2003-2014
 */

namespace OxidEsales\TestingLibrary;

use DateTime;
use PHPUnit_Framework_SkippedTestError as SkippedTestError;

/**
 * Base tests class. Most tests should extend this class.
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{

    /** @var TestConfig */
    private static $testConfig;

    /**
     * Returns test configuration.
     *
     * @return TestConfig
     */
    public function getTestConfig()
    {
        return self::getStaticTestConfig();
    }

    /**
     * Returns test configuration.
     *
     * @return TestConfig
     */
    public static function getStaticTestConfig()
    {
        if (is_null(self::$testConfig)) {
            self::$testConfig = new TestConfig();
        }

        return self::$testConfig;
    }

    /**
     * Mark the test as skipped until given date.
     * Wrapper function for PHPUnit_Framework_Assert::markTestSkipped.
     *
     * @param string $sDate    Date string in format 'Y-m-d'.
     * @param string $sMessage Message.
     *
     * @throws SkippedTestError
     */
    public function markTestSkippedUntil($sDate, $sMessage = '')
    {
        $oDate = DateTime::createFromFormat('Y-m-d', $sDate);

        if (time() < ((int) $oDate->format('U'))) {
            $this->markTestSkipped($sMessage);
        }
    }

    /**
     * @param mixed  $exceptionName
     * @param string $exceptionMessage
     * @param int    $exceptionCode
     *
     * @since  Method available since Release 3.2.0
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        if (!is_null($exceptionName)) {
            $this->stubExceptionToNotWriteToLog($exceptionName, $exceptionName);
        }
        parent::setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null);
    }


    /**
     * This method should be tied to setExpectedException and thus be private
     *
     * OnlineCaller rethrows exception in method _castExceptionAndWriteToLog
     * this way we mock it from writing to log.
     *
     * @param string $exceptionClassName The name of the exception we want to stub, to not log its output.
     * @param string $saveUnderClassName The name under which we save the stubbed exception in the testing library.
     *
     * @return MockObject The mocked exception.
     */
    protected function stubExceptionToNotWriteToLog($exceptionClassName = 'oxException', $saveUnderClassName = 'oxException')
    {
        try {
            $exception = $this->getMock($exceptionClassName, ['debugOut']);
            $exception->expects($this->any())->method('debugOut');

            \oxTestModules::addModuleObject($saveUnderClassName, $exception);
        } catch (\PHPUnit_Framework_Error_Warning $exception) {
            // This may happen, if no subclass of StandardException is passed as a parameter
        }
    }

    /**
     * Activates the theme for running acceptance tests on.
     *
     * @todo Refactor this method to use ThemeSwitcher service. This will require a prior refactoring of the testing library.
     *
     * @param string $themeName Name of the theme to activate
     */
    public function activateTheme($themeName)
    {
        $currentShopId = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();

        $theme = oxNew(\OxidEsales\Eshop\Core\Theme::class);
        $theme->load($themeName);

        $testConfig = new TestConfig();
        $shopId = $testConfig->getShopId();
        \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($shopId);

        $theme->activate();

        /**
         * In the tests, the main shops' theme always hay to be switched too.
         * If the current shop is not a parent shop (i.e. shopId == 1), activate the theme in the parent shop as well.
         */
        if ($shopId != 1) {
            \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId(1);

            $theme->activate();
        }
        \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($currentShopId);
    }

    /**
     * Initialize the fixture.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->failOnLoggedExceptions();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->failOnLoggedExceptions();
    }

    protected function failOnLoggedExceptions()
    {
        if($exceptionLogEntries = file_get_contents(OX_LOG_FILE)) {
            $this->clearExceptionLog();
            $this->fail('Test failed with exception:' .PHP_EOL . $exceptionLogEntries);
        }
    }

    /**
     * Use this in _justified_ cases to clear exception log, e.g. if you are testing  exceptions and their behavior.
     * Do _not_ use this to silence exceptions, if you do not understand why they are thrown.
     */
    protected function clearExceptionLog() {
        fclose(fopen(OX_LOG_FILE,'w'));
    }
}
