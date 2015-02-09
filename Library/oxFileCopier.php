<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link http://www.oxid-esales.com
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
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
            $this->_executeCommand("scp -rp ".escapeshellarg($sSource."/.")." ".escapeshellarg($sTarget));
            if ($blSetPermissions) {
                list($sServer, $sDirectory) = explode(":", $sTarget, 2);
                $this->_executeCommand("ssh ".escapeshellarg($sServer)." chmod 777 ".escapeshellarg('/'.$sDirectory));
            }
        } else {
            $this->_executeCommand("cp -frT ".escapeshellarg($sSource)." ".escapeshellarg($sTarget));
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
     * @param string $dir Path to directory.
     * @param bool $rmBaseDir Whether to delete base directory.
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
