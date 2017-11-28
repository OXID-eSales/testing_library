<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Integration;

use OxidEsales\TestingLibrary\Services\Library\CliExecutor;
use PHPUnit_Framework_TestCase;

class ChangeExceptionLogRightsTest extends PHPUnit_Framework_TestCase
{
    public function testThrowsNoExceptionWhenCorrectCommand()
    {
        $this->assertNull(CliExecutor::executeCommand('php -v'));
    }

    /**
     * @expectedException \Exception
     */
    public function testThrowsExceptionWhenWrongCommand()
    {
        CliExecutor::executeCommand('NotExistingCommand -v');
    }
}
