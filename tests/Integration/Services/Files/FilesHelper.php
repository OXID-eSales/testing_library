<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Tests\Integration\Services\Files;

use org\bovigo\vfs\vfsStream;

/**
 * Wrapper for a vfsStream.
 * Simplifies creation/usages of virtual directory structure.
 */
class FilesHelper
{
    /**
     * Simplifies creation of virtual directory structure.
     *
     * @param array $structure possibility to provide custom structure.
     * @param string $rootDirectoryName possibility to provide custom name for a file root directory.
     * @param int $rights possibility to provide custom rights for a files.
     *
     * @return string
     */
    static function prepareStructureAndReturnPath($structure = [], $rootDirectoryName = 'root', $rights = 0777)
    {
        $vfsStream = vfsStream::setup($rootDirectoryName, $rights, $structure);
        $rootPath = $vfsStream->url();

        return $rootPath;
    }
}
