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
namespace OxidEsales\TestingLibrary\Services\Library;

use oxConfig;

/**
 * Class used for uploading files in services.
 */
class ServiceConfig
{
    const EDITION_ENTERPRISE = 'EE';

    const EDITION_PROFESSIONAL = 'PE';

    const EDITION_COMMUNITY = 'CE';

    /** @var string Tested shop directory. */
    private $shopDirectory;

    /** @var string Shop edition. */
    private $shopEdition;

    /** @var string Temporary directory to store temp files. */
    private $tempDirectory;

    /** @var string Shop edition suffix. */
    private $editionSuffix;

    /**
     * Sets default values.
     */
    public function __construct()
    {
        $this->shopDirectory = __DIR__ . '/../../';
        $this->tempDirectory = __DIR__ .'/../temp/';
    }

    /**
     * Returns path to shop source directory.
     * If shop path was not set, it assumes that services was copied to shop root directory.
     *
     * @return string
     */
    public function getShopDirectory()
    {
        return $this->shopDirectory;
    }

    /**
     * Sets shop path.
     *
     * @param string $shopDirectory
     */
    public function setShopDirectory($shopDirectory)
    {
        $this->shopDirectory = $shopDirectory;
    }


    /**
     * Returns shop edition
     *
     * @return array|null|string
     */
    public function getShopEdition()
    {
        if (is_null($this->shopEdition)) {
            $shopPath = $this->getShopDirectory();
            include_once $shopPath . 'core/oxsupercfg.php';
            include_once $shopPath . 'core/oxconfig.php';
            $config = new oxConfig();
            $shopEdition = $config->getEdition();

            $this->shopEdition = strtoupper($shopEdition);
        }
        return $this->shopEdition;
    }

    /**
     * Sets shop path.
     *
     * @param string $shopEdition
     */
    public function setShopEdition($shopEdition)
    {
        $this->shopEdition = $shopEdition;
    }

    /**
     * Returns shop edition suffix
     *
     * @return string
     */
    public function getEditionSufix()
    {
        if (is_null($this->editionSuffix)) {
            $versionDefinePath = $this->getShopDirectory() ."_version_define.php";
            if (!defined('OXID_VERSION_SUFIX') && file_exists($versionDefinePath)) {
                include $versionDefinePath;
            }
            $this->editionSuffix = defined('OXID_VERSION_SUFIX') ? OXID_VERSION_SUFIX : '';
        }
        return $this->editionSuffix;
    }

    /**
     * Returns temp path.
     *
     * @return string
     */
    public function getTempDirectory()
    {
        if (!file_exists($this->tempDirectory)) {
            mkdir($this->tempDirectory, 0777);
            chmod($this->tempDirectory, 0777);
        }

        return $this->tempDirectory;
    }

    /**
     * Set temp path.
     *
     * @param string $tempPath
     */
    public function setTempDirectory($tempPath)
    {
        $this->tempDirectory = $tempPath;
    }

    /**
     * Returns services root directory.
     *
     * @return string
     */
    public function getServicesDirectory()
    {
        return __DIR__ .'/../';
    }
}
