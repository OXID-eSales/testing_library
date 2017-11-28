<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use \org\bovigo\vfs\vfsStream;
use \org\bovigo\vfs\vfsStreamDirectory;

/**
 * VfsStream wrapper class. This class should be used to work with vfsStreams while testing to
 * avoid problems.
 */
class VfsStreamWrapper
{
    const ROOT_DIRECTORY = 'root';

    /** @var vfsStreamDirectory */
    private $root;

    /**
     * Creates new instance of vfsStreamDirectory.
     */
    public function __construct()
    {
        $this->root = vfsStream::setup(self::ROOT_DIRECTORY);
    }

    /**
     * Creates file with given content.
     * If file contains path, directories will also be created.
     * Creating multiple files in the same directory does not work as
     * parent directories gets cleared on creation.
     *
     * NOTE: this can be used only once! If you call it twice,
     *       the first file is gone and not found by is_file,
     *       file_exists and others!
     *
     * @param string $filePath
     * @param string $content  Will try to convent any value to string if non string is given.
     *
     * @return string Path to created file.
     */
    public function createFile($filePath, $content = '')
    {
        $this->createStructure([ltrim($filePath, '/') => $content]);
        return $this->getRootPath() . $filePath;
    }

    /**
     * Creates whole directory structure.
     * Structure example: ['dir' => ['subdir' => ['file' => 'content']]].
     *
     * @param array $structure
     *
     * @return string Path to root directory
     */
    public function createStructure($structure)
    {
        vfsStream::create($this->prepareStructure($structure), $this->getRoot());

        return $this->getRootPath();
    }

    /**
     * Returns root url. It should be treated as usual file path.
     *
     * @return string
     */
    public function getRootPath()
    {
        return vfsStream::url(self::ROOT_DIRECTORY) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns vfsStream root directory.
     * Root directory will only be created once, as recreating will cause
     * destroyal of the old one and of all the files created.
     *
     * @return vfsStreamDirectory
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    private function prepareStructure($structure)
    {
        $newStructure = [];
        foreach ($structure as $path => $element) {
            $position = &$newStructure;
            foreach (explode('/', $path) as $part) {
                $position[$part] = [];
                $position = &$position[$part];
            }
            $position = strpos($path, DIRECTORY_SEPARATOR) === false ? [] : $position;
            $position = is_array($element) ? $this->prepareStructure($element) : (string) $element;
        }
        return $newStructure;
    }

}
