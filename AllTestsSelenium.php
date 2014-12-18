<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

require_once 'AllTestsRunner.php';

/**
 * PHPUnit_Framework_TestCase implementation for adding and testing all selenium tests from this dir
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
        return '*Test.php';
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
