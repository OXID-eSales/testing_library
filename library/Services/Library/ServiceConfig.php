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



/**
 * Class used for uploading files in services.
 */
class ServiceConfig
{
    const EDITION_ENTERPRISE = 'EE';

    const EDITION_PROFESSIONAL = 'PE';

    const EDITION_COMMUNITY = 'CE';

    /** @var string Tested OXID eShop directory. */
    private $shopDirectory;

    /** @var string The OXID eShop edition. */
    private $shopEdition;

    /** @var string Temporary directory to store temp files. */
    private $tempDirectory;

    /**
     * Sets default values.
     *
     * @param string $shopDirectory
     * @param string $tempDirectory
     */
    public function __construct($shopDirectory, $tempDirectory = '')
    {
        $this->shopDirectory = $shopDirectory;
        if (empty($tempDirectory)) {
            $tempDirectory = $shopDirectory . '/temp';
        }
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * Returns path to OXID eShop source directory.
     * If OXID eShop path was not set, it assumes that services was copied to OXID eShop root directory.
     *
     * @return string
     */
    public function getShopDirectory()
    {
        return $this->shopDirectory;
    }

    /**
     * Sets OXID eShop path.
     *
     * @param string $shopDirectory
     */
    public function setShopDirectory($shopDirectory)
    {
        $this->shopDirectory = $shopDirectory;
    }


    /**
     * Returns OXID eShop edition
     *
     * @return array|null|string
     */
    public function getShopEdition()
    {
        if (is_null($this->shopEdition)) {
            $editionSelector = new \OxidEsales\Facts\Edition\EditionSelector();

            $this->shopEdition = strtoupper($editionSelector->getEdition());
        }
        return $this->shopEdition;
    }

    /**
     * Sets OXID eShop path.
     *
     * @param string $shopEdition
     */
    public function setShopEdition($shopEdition)
    {
        $this->shopEdition = $shopEdition;
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
