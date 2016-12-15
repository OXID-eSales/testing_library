<?php
/**
 * Created by PhpStorm.
 * User: saulius stasiukaitis
 * Date: 12/13/2016
 * Time: 10:40 AM
 */

namespace OxidEsales\TestingLibrary\Tests\Integration\Services\Files;

use org\bovigo\vfs\vfsStream;

class FilesHelper
{
    /**
     * @return string
     */
    static function prepareStructureAndReturnPath()
    {
        $structure = array(
            'testDirectory' => [
                'someFile.php' => 'content',
                'someFile2.php' => 'content',
            ]
        );
        $vfsStream = vfsStream::setup('root', '777', $structure);
        $rootPath = $vfsStream->url();

        return $rootPath;
    }
}
