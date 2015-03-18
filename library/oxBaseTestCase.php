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
}
