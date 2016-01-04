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

use OxidEsales\TestingLibrary\VfsStreamWrapper;

class oxVfsStreamWrapperTest extends PHPUnit_Framework_TestCase
{
    public function testCreationOfRoot()
    {
        $vfsStreamWrapper = new VfsStreamWrapper();

        $this->assertInstanceOf('\org\bovigo\vfs\vfsStreamDirectory', $vfsStreamWrapper->getRoot());
    }

    public function testReturningTheSameRootOnEveryCall()
    {
        $vfsStreamWrapper = new VfsStreamWrapper();
        $root = $vfsStreamWrapper->getRoot();

        $this->assertSame($root, $vfsStreamWrapper->getRoot());
    }

    public function testReturningCorrectRootPath()
    {
        $vfsStreamWrapper = new VfsStreamWrapper();

        $this->assertEquals('vfs://root/', $vfsStreamWrapper->getRootPath());
    }

    public function testFileCreation()
    {
        $vfsStreamWrapper = new VfsStreamWrapper();
        $filePath = $vfsStreamWrapper->createFile('testFile.txt', 'content');

        $this->assertTrue(file_exists($filePath));
        $this->assertEquals('content', file_get_contents($filePath));
    }

    public function providerCreateFile()
    {
        return array(
            array('path'),
            array('path/to/file'),
            array('/path/to/file')
        );
    }

    /**
     * @dataProvider providerCreateFile
     *
     * @param string $directory
     */
    public function testCreateFile($directory)
    {
        $vfsStream = new VfsStreamWrapper();
        $file = $vfsStream->createFile($directory .'/testFile.txt', 'content');
        $rootPath = $vfsStream->getRootPath();

        $this->assertEquals($rootPath . $directory .'/testFile.txt', $file);
        $this->assertTrue(is_dir($rootPath . $directory));
        $this->assertTrue(file_exists($file));
    }

    public function testCreatingMultipleFiles()
    {
        $vfsStreamWrapper = new VfsStreamWrapper();
        $file1 = $vfsStreamWrapper->createFile('testFile1.txt', 'content1');
        $file2 = $vfsStreamWrapper->createFile('testFile2.txt', 'content2');

        $this->assertTrue(file_exists($file1));
        $this->assertEquals('content1', file_get_contents($file1));
        $this->assertTrue(file_exists($file2));
        $this->assertEquals('content2', file_get_contents($file2));
    }

    public function testStructureCreation()
    {
        $structure = array(
            'dir' => array(
                'subdir' => array(
                    'testFile' => 'content'
                )
            )
        );

        $vfsStreamWrapper = new VfsStreamWrapper();

        $vfsStreamWrapper->createStructure($structure);
        $rootPath = $vfsStreamWrapper->getRootPath();

        $this->assertTrue(is_dir($rootPath .'dir'));
        $this->assertTrue(is_dir($rootPath .'dir/subdir'));
        $this->assertEquals('content', file_get_contents($rootPath .'dir/subdir/testFile'));
    }

    public function testReturningRootDirectoryAfterStructureCreation()
    {
        $structure = array();

        $vfsStreamWrapper = new VfsStreamWrapper();

        $returnedPath = $vfsStreamWrapper->createStructure($structure);
        $expectedPath = $vfsStreamWrapper->getRootPath();

        $this->assertEquals($returnedPath, $expectedPath);
    }
}
