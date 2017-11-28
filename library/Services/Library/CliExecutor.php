<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Library;

use Exception;

class CliExecutor
{
    /**
     * Execute shell command.
     * Throw an exception in if shell command fails.
     *
     * @param string $command
     *
     * @throws Exception
     */
    static function executeCommand($command)
    {
        exec($command, $output, $resultCode);

        if ($resultCode > 0) {
            $output = implode("\n", $output);
            throw new Exception("Failed to execute command: '$command' with output: '$output' ");
        }
    }
}
