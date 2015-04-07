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

/**
 * Class used for uploading files in services.
 */
class ServiceConfig
{
    private $shopPath;
    private $shopEdition;
    private $tempPath;

    /**
     * Returns path to shop source directory.
     *
     * @return string
     */
    public function getShopPath()
    {
        if (is_null($this->shopPath)) {
            $this->shopPath = realpath(__DIR__ . '/../../').'/';
        }
        return $this->shopPath;
    }

    /**
     * Sets shop path.
     *
     * @param string $shopPath
     */
    public function setShopPath($shopPath)
    {
        $this->shopPath = $shopPath;
    }


    /**
     * Returns shop edition
     *
     * @return array|null|string
     */
    public function getShopEdition()
    {
        if (is_null($this->shopEdition)) {
            $shopPath = $this->getShopPath();
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
        $versionDefinePath = $this->getShopPath() ."_version_define.php";
        if (!defined('OXID_VERSION_SUFIX') && file_exists($versionDefinePath)) {
            include $versionDefinePath;
        }

        return defined('OXID_VERSION_SUFIX') ? OXID_VERSION_SUFIX : '';
    }

    /**
     * Returns temp path.
     *
     * @return string
     */
    public function getTempPath()
    {
        if (is_null($this->tempPath)) {
            $this->tempPath = __DIR__ .'/../temp/';

            if (!file_exists($this->tempPath)) {
                mkdir($this->tempPath, 0777);
                chmod($this->tempPath, 0777);
            }
        }
        return $this->tempPath;
    }

    /**
     * Set temp path.
     *
     * @param string $tempPath
     */
    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }
}
