<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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

    /** @var string Tested shop directory. */
    private $shopDirectory;

    /** @var string Shop edition. */
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
            $config = new \OxidEsales\Eshop\Core\Config();
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
