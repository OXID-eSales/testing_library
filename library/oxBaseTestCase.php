<?php


/**
 * Base tests class. Most tests should extend this class.
 */
class oxBaseTestCase extends PHPUnit_Framework_TestCase
{
    /** @var Test_Config */
    private static $testConfig;

    /**
     * Returns test configuration.
     *
     * @return Test_Config
     */
    public function getTestConfig()
    {
        if (is_null(self::$testConfig)) {
            self::$testConfig = new Test_Config();
        }
        return self::$testConfig;
    }
}
