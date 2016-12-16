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
 * @copyright (C) OXID eSales AG 2003-2016
 */

namespace OxidEsales\TestingLibrary\Services\Files;

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use Symfony\Component\Filesystem\Filesystem;
use oxRegistry;

class ChangeRights implements ShopServiceInterface
{
    const FILES_PARAMETER_PATH = 'filesRootPath';

    const FILES_PARAMETER_NAME = 'files';

    const FILES_PARAMETER_RIGHTS = 'rights';

    /** @var ServiceConfig */
    private $serviceConfig;

    /** @var Filesystem */
    private $fileSystem;

    /**
     * Remove constructor.
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->serviceConfig = $config;
        $this->fileSystem = new Filesystem();
    }

    /**
     * @param \OxidEsales\TestingLibrary\Services\Library\Request $request
     */
    public function init($request)
    {
        $fileRights = $request->getParameter(static::FILES_PARAMETER_RIGHTS);
        $fileName = $request->getParameter(static::FILES_PARAMETER_NAME);
        $filesRootDirectory = $request->getParameter(static::FILES_PARAMETER_PATH);

        $pathToShop = $this->getFilesRootPath($filesRootDirectory);
        $filesToUpdate = $this->addPathToFileNames($fileName, $pathToShop);
        if ($this->fileSystem->exists($filesToUpdate)) {
            $this->fileSystem->chmod($filesToUpdate, $fileRights);
        }
    }

    /**
     * @param array $filesName
     * @param string $filesRootPath
     *
     * @return array
     */
    private function addPathToFileNames($filesName, $filesRootPath)
    {
        $fileNameWithPath = [];

        foreach ($filesName as $fileName) {
            $fileNameWithPath[] = $filesRootPath . DIRECTORY_SEPARATOR . $fileName;
        }

        return $fileNameWithPath;
    }

    /**
     * Return path to the directory where files are located.
     *
     * @param string $filesRootDirectory
     *
     * @return string
     */
    private function getFilesRootPath($filesRootDirectory)
    {
        if (!$filesRootDirectory) {
            $filesRootDirectory = oxRegistry::get("oxConfigFile")->getVar('sShopDir');
        }

        return $filesRootDirectory;
    }
}
