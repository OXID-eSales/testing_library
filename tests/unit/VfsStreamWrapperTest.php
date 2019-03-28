<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\TestingLibrary\VfsStreamWrapper;

class VfsStreamWrapperTest extends PHPUnit\Framework\TestCase
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
        $this->assertFileExists($file);
    }

    public function testCreateFileWithNumericContent()
    {
        $vfsStream = new VfsStreamWrapper();
        $file = $vfsStream->createFile('testFile.txt', 1234);

        $this->assertStringEqualsFile($file, '1234');
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
            ),
            'dir2' => array(
                'subdir2' => array(
                    'testFile2' => 'content'
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

    public function testStructureCreationWithPathsSpecified()
    {
        $structure = array(
            'dir/subdir/testFile' => 'content',
            'dir2/subdir/testFile' => 'content2',
            'dir3/subdir3/testFile3' => 'content3',
            'dir4' => [
                'subdir4/testFile4' => 'content4',
            ]
        );

        $vfsStreamWrapper = new VfsStreamWrapper();

        $vfsStreamWrapper->createStructure($structure);
        $rootPath = $vfsStreamWrapper->getRootPath();

        $this->assertEquals('content', file_get_contents($rootPath .'dir/subdir/testFile'));
        $this->assertEquals('content2', file_get_contents($rootPath .'dir2/subdir/testFile'));
        $this->assertEquals('content3', file_get_contents($rootPath .'dir3/subdir3/testFile3'));
        $this->assertEquals('content4', file_get_contents($rootPath .'dir4/subdir4/testFile4'));
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
