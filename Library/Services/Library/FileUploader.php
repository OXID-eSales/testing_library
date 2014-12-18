<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

/**
 * Class used for uploading files in services.
 */
class FileUploader
{
    /**
     * Uploads file to given location.
     *
     * @param string $sFileIndex file index
     * @param string $sLocation  location where to put uploaded file
     * @param bool $blOverwrite  whether to overwrite existing file
     * @throws Exception         throws exception if file with given index does not exist.
     * @return bool              whether upload succeeded
     */
    public function uploadFile($sFileIndex, $sLocation, $blOverwrite = true)
    {
        $aFileInfo = $this->_getFileInfo($sFileIndex);

        if (!$this->_checkFile($aFileInfo)) {
            throw new Exception("File with index '$sFileIndex' does not exist or error occurred while downloading it");
        }

        return $this->_moveUploadedFile($aFileInfo, $sLocation, $blOverwrite);
    }

    /**
     * @param $aFileInfo
     * @return bool
     */
    private function _checkFile($aFileInfo)
    {
        $blResult = isset($aFileInfo['name']) && isset($aFileInfo['tmp_name']);

        if ($blResult && isset($aFileInfo['error']) && $aFileInfo['error']) {
            $blResult = false;
        }

        return $blResult;
    }

    /**
     * @param $sFileIndex
     * @return null
     */
    private function _getFileInfo($sFileIndex)
    {
        return $_FILES[$sFileIndex];
    }

    /**
     * @param $aFileInfo
     * @param $sLocation
     * @param $blOverwrite
     * @return bool
     */
    private function _moveUploadedFile($aFileInfo, $sLocation, $blOverwrite)
    {
        $blDone = false;

        if (!file_exists($sLocation) || $blOverwrite) {
            $blDone = move_uploaded_file($aFileInfo['tmp_name'], $sLocation);

            if ($blDone) {
                $blDone = @chmod($sLocation, 0644);
            }
        }

        return $blDone;
    }
}