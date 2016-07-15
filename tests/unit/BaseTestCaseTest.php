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

use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * Empty non abstract class for testing the unit test case.
 */
class testCaseBase extends \OxidEsales\TestingLibrary\BaseTestCase
{

}

/**
 * Test for the BaseTestCase class.
 */
class BaseTestCaseTest extends UnitTestCase
{

    /**
     * Test, that the method 'markTestSkippedUntil' skips the test, if the given date is after now.
     */
    public function testMarkTestSkippedUntilSkipsTest()
    {
        $baseTestCase = $this->getMock('testCaseBase', ['isAfterNow']);
        $baseTestCase->expects($this->once())->method('isAfterNow')->willReturn(true);

        $expectedMessage = 'THE EXPECTED MESSAGE!';

        try {
            $baseTestCase->markTestSkippedUntil('1999-12-31', $expectedMessage);
        } catch (Exception $exception) {
            $this->assertSame($expectedMessage, $exception->getMessage());
        }
    }

    /**
     * Test, that the method 'markTestSkippedUntil' doesn't skip the test, if the given date is not after now.
     */
    public function testMarkTestSkippedUntilNotSkipsTest()
    {
        $baseTestCase = $this->getMock('testCaseBase', ['isAfterNow']);
        $baseTestCase->expects($this->once())->method('isAfterNow')->willReturn(false);

        $message = 'THE EXPECTED MESSAGE!';

        $baseTestCase->markTestSkippedUntil('1999-12-31', $message);
    }

}
