<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark that test should be retried.
 */
class RetryTestException extends \PHPUnit_Framework_AssertionFailedError
{
}
