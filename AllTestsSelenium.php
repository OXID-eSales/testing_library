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

require_once 'AllTestsRunner.php';

/**
 * This class is used to run all CE|PE|EE edition selenium tests.
 */
class AllTestsSelenium extends AllTestsRunner
{

    /** @var array Default test suites */
    protected static $_aTestSuites = array('acceptance');

    /** @var array Run these tests before any other */
    protected static $_aPriorityTests = array('acceptance/Frontend/shopSetUpTest.php');

    /**
     * Returns test files filter
     *
     * @return string
     */
    protected static function _getTestFileFilter()
    {
        return '*Test\.php';
    }

    /**
     * Forms class name from file name.
     *
     * @param string $sFilename
     *
     * @return string
     */
    protected static function _formClassNameFromFileName($sFilename)
    {
        $sFilename = str_replace('acceptance/', '', $sFilename);
        return str_replace(array("/", ".php"), array("_", ""), $sFilename);
    }
}
