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
    protected static $_aTestSuites = array('integration');
}
