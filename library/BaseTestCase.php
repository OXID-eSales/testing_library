<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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

    protected $exceptionLogHelper;


    /**
     * BaseTestCase constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->exceptionLogHelper = new \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper(OX_LOG_FILE);
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
     * Returns test configuration.
     *
     * @return TestConfig
     */
    public function getTestConfig()
    {
        return self::getStaticTestConfig();
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
     * Activates the theme for running acceptance tests on.
     *
     * @todo Refactor this method to use ThemeSwitcher service. This will require a prior refactoring of the testing library.
     *
     * @param string $themeName Name of the theme to activate
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
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
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->failOnLoggedExceptions();
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->failOnLoggedExceptions();
    }

    /**
     * @param string      $expectedExceptionClass
     * @param string|null $expectedExceptionMessage
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    protected function assertLoggedException($expectedExceptionClass, $expectedExceptionMessage = null)
    {
        $parsedExceptions = $this->exceptionLogHelper->getParsedExceptions();

        $actualExceptionCount = count($parsedExceptions);
        $actualExceptionClass = $parsedExceptions[0]['type'];
        $actualExceptionMessage = $parsedExceptions[0]['message'];
        $exceptionLogEntries = $this->exceptionLogHelper->getExceptionLogFileContent();

        $this->exceptionLogHelper->clearExceptionLogFile();

        $this->assertSame(
            1,
            $actualExceptionCount,
            'Only one exception is expected to be logged' . PHP_EOL .
            $exceptionLogEntries
        );
        $this->assertSame(
            $expectedExceptionClass,
            $actualExceptionClass,
            'The logged exception should be an instance of ' . $expectedExceptionClass . PHP_EOL .
            $exceptionLogEntries
        );
        if ($expectedExceptionMessage) {
            $this->assertSame(
                $expectedExceptionMessage,
                $actualExceptionMessage,
                'The logged exception message should be "' . $expectedExceptionMessage . '"' . PHP_EOL .
                $exceptionLogEntries
            );
        }
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    protected function failOnLoggedExceptions()
    {
        if ($exceptionLogEntries = $this->exceptionLogHelper->getExceptionLogFileContent()) {
            $this->exceptionLogHelper->clearExceptionLogFile();
            $this->fail('Test failed with ' . OX_LOG_FILE . ' entry:' . PHP_EOL . PHP_EOL . $exceptionLogEntries);
        }
    }
}
