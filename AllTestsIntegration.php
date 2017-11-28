<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

require_once 'AllTestsRunner.php';

/**
 * This class is used to run all CE|PE|EE edition integration tests.
 */
class AllTestsIntegration extends AllTestsRunner
{

    /** @var array Default test suites */
    protected static $_aTestSuites = array('integration', 'Integration');
}
