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

use org\bovigo\vfs\vfsStream;
use OxidEsales\TestingLibrary\FileCopier;

class oxFileCopierTest extends PHPUnit_Framework_TestCase
{
    public function testCopyLocalFile()
    {
        $expectedCommand = "cp -frT 'source' 'target'";

        /** @var FileCopier|PHPUnit_Framework_MockObject_MockObject $fileCopier */
        $fileCopier = $this->getMock('\OxidEsales\TestingLibrary\FileCopier', array('executeCommand'));
        $fileCopier->expects($this->once())->method('executeCommand')->with($this->equalTo($expectedCommand));

        $fileCopier->copyFiles('source', 'target');
    }

    public function testCopyLocalFileWithPermissions()
    {
        $expectedCommand = "cp -frT 'source' 'target' && chmod 777 'target'";

        /** @var FileCopier|PHPUnit_Framework_MockObject_MockObject $fileCopier */
        $fileCopier = $this->getMock('\OxidEsales\TestingLibrary\FileCopier', array('executeCommand'));
        $fileCopier->expects($this->once())->method('executeCommand')->with($this->equalTo($expectedCommand));

        $fileCopier->copyFiles('source', 'target', true);
    }

    public function testCopyRemoteFile()
    {
        $expectedCommand = "scp -rp 'source' 'user@host:/target'";

        /** @var FileCopier|PHPUnit_Framework_MockObject_MockObject $fileCopier */
        $fileCopier = $this->getMock('\OxidEsales\TestingLibrary\FileCopier', array('executeCommand'));
        $fileCopier->expects($this->once())->method('executeCommand')->with($this->equalTo($expectedCommand));

        $fileCopier->copyFiles('source', 'user@host:/target');
    }

    public function testCopyRemoteFileWithPermissions()
    {
        $expectedCommand = "rsync -rp --perms --chmod=u+rwx,g+rwx,o+rwx 'source' 'user@host:/target'";

        /** @var FileCopier|PHPUnit_Framework_MockObject_MockObject $fileCopier */
        $fileCopier = $this->getMock('\OxidEsales\TestingLibrary\FileCopier', array('executeCommand'));
        $fileCopier->expects($this->once())->method('executeCommand')->with($this->equalTo($expectedCommand));

        $fileCopier->copyFiles('source', 'user@host:/target', true);
    }

    public function testEmptyDirectoryCreationWhenDirectoryDoesNotExist()
    {
        $structure = array(
            'testDirectory' => array()
        );

        vfsStream::setup('root', 777, $structure);

        $newDirectory = vfsStream::url('root/testDirectory/emptyDirectory');

        $fileCopier = new FileCopier();
        $fileCopier->createEmptyDirectory($newDirectory);

        $this->assertTrue(is_dir($newDirectory));
        $this->assertEquals(2, count(scandir($newDirectory)));
    }

    public function testEmptyDirectoryCreationWhenDirectoryExist()
    {
        $structure = array(
            'testDirectory' => array(
                'nonEmptyDirectory' => array(
                    'someFile.php' => 'content',
                    'someFile2.php' => 'content',
                    'directory' => array(
                        'someFile' => 'content'
                    )
                )
            )
        );

        vfsStream::setup('root', 777, $structure);

        $newDirectory = vfsStream::url('root/testDirectory/nonEmptyDirectory');

        $fileCopier = new FileCopier();
        $fileCopier->createEmptyDirectory($newDirectory);

        $this->assertTrue(is_dir($newDirectory));
        $this->assertEquals(2, count(scandir($newDirectory)));
    }
}
