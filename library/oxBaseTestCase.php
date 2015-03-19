<?php


/**
 * Base tests class. Most tests should extend this class.
 */
class oxBaseTestCase extends PHPUnit_Framework_TestCase
{
    /** @var oxTestConfig */
    private static $testConfig;

    /**
     * Returns test configuration.
     *
     * @return oxTestConfig
     */
    public function getTestConfig()
    {
        if (is_null(self::$testConfig)) {
            self::$testConfig = new oxTestConfig();
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
     * @throws PHPUnit_Framework_SkippedTestError
     */
    public function markTestSkippedUntil($sDate, $sMessage = '')
    {
        $oDate = DateTime::createFromFormat('Y-m-d', $sDate);

        if (time() < ((int)$oDate->format('U'))) {
            $this->markTestSkipped($sMessage);
        }
    }
}
