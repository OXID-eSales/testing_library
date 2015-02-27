<?php
/**
 * This file contains the script required to run all PE edition unit tests in unit dir on Cruise Control.
 * This file is supposed to be executed over PHPUnit framework
 * It is called something like this:
 * phpunit <Test dir>_AllTests
 *
 * @link          http://www.oxid-esales.com
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

echo "=========\nrunning php version " . phpversion() . "\n\n============\n";

/**
 * PHPUnit_Framework_TestCase implementation for adding and testing all unit tests from unit dir
 */
class AllTestsRunner extends PHPUnit_Framework_TestCase
{

    /** @var array Default test suites */
    protected static $_aTestSuites = array();

    /** @var array Run these tests before any other */
    protected static $_aPriorityTests = array();

    /**
     * Forms test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $aTestDirectories = static::_getTestDirectories();

        $oSuite = new PHPUnit_Framework_TestSuite('PHPUnit');

        static::_addPriorityTests($oSuite, static::$_aPriorityTests, $aTestDirectories);

        foreach ($aTestDirectories as $sDirectory) {
            $sFilesSelector = "$sDirectory/" . static::_getTestFileFilter();
            $aTestFiles = glob($sFilesSelector);

            if (empty($aTestFiles)) {
                continue;
            }

            echo "Adding unit tests from $sFilesSelector\n";

            $aTestFiles = array_diff($aTestFiles, static::$_aPriorityTests);
            $oSuite = static::_addFilesToSuite($oSuite, $aTestFiles);
        }

        return $oSuite;
    }

    /**
     * Adds tests with highest priority.
     *
     * @param PHPUnit_Framework_TestSuite $oSuite
     * @param array                       $aPriorityTests
     * @param array                       $aTestDirectories
     */
    public static function _addPriorityTests($oSuite, $aPriorityTests, $aTestDirectories)
    {
        if (!empty($aPriorityTests)) {
            $aTestsToInclude = array();
            foreach ($aPriorityTests as $sTestFile) {
                $sFolder = dirname($sTestFile);
                $aDirectories = array_filter($aTestDirectories, function($sTestDirectory) use ($sFolder){
                    return (substr($sTestDirectory, -strlen($sFolder)) === $sFolder);
                });
                if (!empty($aDirectories)) {
                    $fullPath = array_shift($aDirectories);
                    $aTestsToInclude[] = $fullPath.'/'.basename($sTestFile);
                }
            }
            static::_addFilesToSuite($oSuite, $aTestsToInclude);
        }
    }

    /**
     * Returns array of directories, which should be tested
     *
     * @return array
     */
    protected static function _getTestDirectories()
    {
        $aTestDirectories = array();
        $aTestSuites = getenv('TEST_DIRS')? explode(',', getenv('TEST_DIRS')) : static::$_aTestSuites;

        foreach ($aTestSuites as $sSuite) {
            $aTestDirectories = array_merge($aTestDirectories, static::_getSuiteDirectories($sSuite));
        }

        return array_merge($aTestDirectories, static::_getDirectoryTree($aTestDirectories));
    }

    /**
     * Returns test suite directories
     *
     * @param array $sTestSuite
     *
     * @return array
     */
    protected static function _getSuiteDirectories($sTestSuite)
    {
        $aDirectories = array();

        if (RUN_SHOP_TESTS && SHOP_TESTS_PATH) {
            $aDirectories[] = SHOP_TESTS_PATH .$sTestSuite;
        }

        if (RUN_MODULE_TESTS && MODULES_PATH) {
            foreach (explode(',', MODULES_PATH) as $sModulePath) {
                $aDirectories[] = oxPATH .'/modules/'.$sModulePath .'/tests/' .$sTestSuite;
            }
        }

        return $aDirectories;
    }

    /**
     * Scans given tests directories and returns formed directory tree
     *
     * @param array $aDirectories
     *
     * @return array
     */
    protected static function _getDirectoryTree($aDirectories)
    {
        $aTree = array();

        foreach ($aDirectories as $sDirectory) {
            $aTree = array_merge($aTree, array_diff(glob($sDirectory . "/*", GLOB_ONLYDIR), array('.', '..')));
        }

        if (!empty($aTree)) {
            $aTree = array_merge($aTree, static::_getDirectoryTree($aTree));
        }

        return $aTree;
    }

    /**
     * Returns test files filter
     *
     * @return string
     */
    protected static function _getTestFileFilter()
    {
        $sTestFileNameEnd = '*[^8]Test.php';
        if (getenv('OXID_TEST_UTF8')) {
            $sTestFileNameEnd = '*utf8Test.php';
        }

        return $sTestFileNameEnd;
    }

    /**
     * Adds files to test suite
     *
     * @param PHPUnit_Framework_TestSuite $oSuite
     * @param array                       $aTestFiles
     *
     * @return PHPUnit_Framework_TestSuite
     */
    protected static function _addFilesToSuite($oSuite, $aTestFiles)
    {
        foreach ($aTestFiles as $sFilename) {

            $sFilter = getenv('PREG_FILTER');
            if (!$sFilter || preg_match("&$sFilter&i", $sFilename)) {
                $oSuite->addTestFile($sFilename);
            }
        }

        return $oSuite;
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
        return str_replace(array("/", ".php"), array("_", ""), $sFilename);
    }
}
