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

class ChangeExceptionLogRights implements ShopServiceInterface
{
    /** @var ServiceConfig */
    private $serviceConfig;

    /** @var Filesystem */
    private $fileSystem;

    /** @var String partly path to exception log */
    const EXCEPTION_LOG_PATH = 'log' . DIRECTORY_SEPARATOR . 'EXCEPTION_LOG.txt';

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
        $fileSystem = new Filesystem();

        $pathToExceptionLog = $this->serviceConfig->getShopDirectory()
            . DIRECTORY_SEPARATOR . self::EXCEPTION_LOG_PATH;

        if (!$fileSystem->exists([$pathToExceptionLog])) {
            $fileSystem->touch($pathToExceptionLog);
        }
        $fileSystem->chmod($pathToExceptionLog, 0777);
    }
}
