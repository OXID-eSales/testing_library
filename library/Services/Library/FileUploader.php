<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library;

use Exception;

/**
 * Class used for uploading files in services.
 */
class FileUploader
{
    /**
     * Uploads file to given location.
     *
     * @param string $sFileIndex  File index
     * @param string $sLocation   Location where to put uploaded file
     * @param bool   $blOverwrite Whether to overwrite existing file
     *
     * @throws Exception Throws exception if file with given index does not exist.
     *
     * @return bool Whether upload succeeded
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
     * Checks if file information (name and tmp_name) is set and no errors exists.
     *
     * @param array $fileInfo
     *
     * @return bool
     */
    private function _checkFile($fileInfo)
    {
        $result = isset($fileInfo['name']) && isset($fileInfo['tmp_name']);

        if ($result && isset($fileInfo['error']) && $fileInfo['error']) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns file information.
     *
     * @param string $fileIndex
     *
     */
    private function _getFileInfo($fileIndex)
    {
        return $_FILES[$fileIndex];
    }

    /**
     * @param array  $fileInfo
     * @param string $location
     * @param bool   $overwrite
     *
     * @return bool
     */
    private function _moveUploadedFile($fileInfo, $location, $overwrite)
    {
        $isDone = false;

        if (!file_exists($location) || $overwrite) {
            $isDone = move_uploaded_file($fileInfo['tmp_name'], $location);

            if ($isDone) {
                $isDone = @chmod($location, 0644);
            }
        }

        return $isDone;
    }
}
