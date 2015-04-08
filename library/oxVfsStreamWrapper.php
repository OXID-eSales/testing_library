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

use \org\bovigo\vfs\vfsStream;
use \org\bovigo\vfs\vfsStreamDirectory;

/**
 * VfsStream wrapper class. This class should be used to work with vfsStreams while testing to
 * avoid problems.
 */
class oxVfsStreamWrapper
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
     * @param string $filePath
     * @param string $content  Will try to convent any value to string if non string is given.
     *
     * @return string Path to created file.
     */
    public function createFile($filePath, $content = '')
    {
        $fileName = basename($filePath);
        $fileDirectory = ltrim(dirname($filePath), '/');

        $directory = $this->getRoot();
        if (!empty($fileDirectory) && $fileDirectory != '.') {
            $directory = $this->createDirectoryStructure($fileDirectory);
        }

        vfsStream::create(array($fileName => "$content"), $directory);

        return $this->getRootPath() . $filePath;
    }

    /**
     * Creates whole directory structure.
     * Structure example: array('dir' => array('subdir' => array('file' => 'content'))).
     *
     * @param array $structure
     *
     * @return string Path to root directory
     */
    public function createStructure($structure)
    {
        vfsStream::create($structure, $this->getRoot());

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
     * Creates directory structure. Returns the latest child element.
     *
     * @param string $directory
     *
     * @return vfsStreamDirectory
     */
    private function createDirectoryStructure($directory)
    {
        $parent = $this->getRoot();
        foreach (explode('/', $directory) as $part) {
            $parent = vfsStream::newDirectory($part)->at($parent);
        }

        return $parent;
    }

}
