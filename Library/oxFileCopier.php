<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Class for copying files. Can copy files locally or to external server.
 */
class oxFileCopier
{

    /**
     * Copy files to shop
     *
     * @param string $sSource          File or directory to copy.
     * @param string $sTarget          Path where to copy.
     * @param bool   $blSetPermissions Whether to set given Target permissions to 777.
     */
    public function copyFiles($sSource, $sTarget, $blSetPermissions = false)
    {
        if (strpos($sTarget, ':') !== false && strpos($sTarget, '@') !== false) {
            $this->_executeCommand("scp -rp " . escapeshellarg($sSource . "/.") . " " . escapeshellarg($sTarget));
            if ($blSetPermissions) {
                list($sServer, $sDirectory) = explode(":", $sTarget, 2);
                $this->_executeCommand("ssh " . escapeshellarg($sServer) . " chmod 777 " . escapeshellarg('/' . $sDirectory));
            }
        } else {
            $this->_executeCommand("cp -frT " . escapeshellarg($sSource) . " " . escapeshellarg($sTarget));
            if ($blSetPermissions) {
                $this->_executeCommand("chmod 777 " . escapeshellarg($sTarget));
            }
        }
    }

    /**
     * Creates new directory if it does not exists, if exists - clears its content.
     *
     * @param string $sDirectory
     */
    public function createEmptyDirectory($sDirectory)
    {
        if (!is_dir($sDirectory)) {
            mkdir($sDirectory, 0777, true);
        } else {
            $this->deleteTree($sDirectory, false);
        }
    }

    /**
     * Deletes given directory content
     *
     * @param string $dir       Path to directory.
     * @param bool   $rmBaseDir Whether to delete base directory.
     */
    private function deleteTree($dir, $rmBaseDir = false)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteTree("$dir/$file", true) : @unlink("$dir/$file");
        }

        if ($rmBaseDir) {
            @rmdir($dir);
        }
    }

    /**
     * Executes shell command.
     *
     * @param string $sCommand
     *
     * @throws Exception
     *
     * @return string Output of command.
     */
    private function _executeCommand($sCommand)
    {
        $blResult = @exec($sCommand, $sOutput, $iCode);
        $sOutput = implode("\n", $sOutput);

        if ($blResult === false) {
            throw new Exception("Failed to execute command '$sCommand' with message: [$iCode] '$sOutput'");
        }

        return $sOutput;
    }
}
