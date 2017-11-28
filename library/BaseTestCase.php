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

        if (time() < ((int)$oDate->format('U'))) {
            $this->markTestSkipped($sMessage);
        }
    }
}
