<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library;

/**
 * Class used for uploading files in services.
 */
class FileHandler
{
    /**
     * Creates directory with write permissions
     *
     * @param string $directoryPath
     * @param int    $permissions
     */
    public function createDirectory($directoryPath, $permissions = 0777)
    {
        $current = '';
        $parts = array_filter(explode('/', $directoryPath));
        foreach ($parts as $part) {
            $current = "$current/$part";
            if (!empty($part) && !file_exists($current)) {
                mkdir($current, $permissions);
                chmod($current, $permissions);
            }
        }
    }
}
