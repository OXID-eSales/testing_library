<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

require_once 'AllTestsRunner.php';

/**
 * This class is used to run all CE|PE|EE edition selenium tests.
 */
class AllTestsSelenium extends AllTestsRunner
{

    /** @var array Default test suites */
    protected static $testSuites = array('Acceptance', 'acceptance');

    /** @var array Run these tests before any other */
    protected static $priorityTests = array('Acceptance/Frontend/shopSetUpTest.php');
}
