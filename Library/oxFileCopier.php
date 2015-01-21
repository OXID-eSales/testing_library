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
