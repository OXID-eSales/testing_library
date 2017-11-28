<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Files;

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use Symfony\Component\Filesystem\Filesystem;

class Remove implements ShopServiceInterface
{
    const FILES_PARAMETER_NAME = 'files';

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
        $this->fileSystem->remove($request->getParameter(static::FILES_PARAMETER_NAME));
    }
}
