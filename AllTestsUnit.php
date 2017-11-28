<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

require_once 'AllTestsRunner.php';

/**
 * This class is used to run all CE|PE|EE edition unit and integration tests.
 */
class AllTestsUnit extends AllTestsRunner
{

    /** @var array Default test suites */
    protected static $testSuites = array('Unit', 'Integration', 'unit', 'integration');
}
