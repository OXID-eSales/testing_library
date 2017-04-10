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
 * @link http://www.oxid-esales.com
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
     * @return oxTestConfig
     */
    public static function getStaticTestConfig()
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
