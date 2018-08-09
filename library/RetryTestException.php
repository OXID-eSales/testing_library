<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use PHPUnit\Framework\AssertionFailedError;
/**
 * Extension to PHPUnit\Framework\AssertionFailedError to mark that test should be retried.
 */
class RetryTestException extends AssertionFailedError
{
}
