<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use Exception;

/**
 * Class for copying files. Can copy files locally or to external server.
 */
class FileCopier
{

    /**
     * Copy files to shop
     *
     * @param string $source          File or directory to copy.
     * @param string $target          Path where to copy.
     * @param bool   $setPermissions Whether to set given Target permissions to 777.
     */
    public function copyFiles($source, $target, $setPermissions = false)
    {
        if (strpos($target, ':') !== false && strpos($target, '@') !== false) {
            if (is_dir($source)) {
                $source .= "/.";
            }
            $command = "scp -rp " . escapeshellarg($source) . " " . escapeshellarg($target);
            if ($setPermissions) {
                $command = "rsync -rp --perms --chmod=u+rwx,g+rwx,o+rwx " . escapeshellarg($source) . " " . escapeshellarg($target);
            }
        } else {
            $command = "cp -frT " . escapeshellarg($source) . " " . escapeshellarg($target);
            if ($setPermissions) {
                $command .= " && chmod 777 " . escapeshellarg($target);
            }
        }
        $this->executeCommand($command);
    }

    /**
     * Creates new directory if it does not exists, if exists - clears its content.
     *
     * @param string $directory
     */
    public function createEmptyDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        } else {
            $this->deleteTree($directory, false);
        }
    }

    /**
     * Deletes given directory content
     *
     * @param string $directory       Path to directory.
     * @param bool   $removeBaseDir Whether to delete base directory.
     */
    protected function deleteTree($directory, $removeBaseDir = false)
    {
        $files = array_diff(scandir($directory), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? $this->deleteTree("$directory/$file", true) : @unlink("$directory/$file");
        }

        if ($removeBaseDir) {
            @rmdir($directory);
        }
    }

    /**
     * Executes shell command.
     *
     * @param string $command
     *
     * @throws Exception
     *
     * @return string Output of command.
     */
    protected function executeCommand($command)
    {
        $result = @exec($command, $output, $code);
        $output = implode("\n", $output);

        if ($result === false) {
            throw new Exception("Failed to execute command '$command' with message: [$code] '$output'");
        }

        return $output;
    }
}
