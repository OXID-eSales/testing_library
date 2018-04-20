<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Files;

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Calling service with different user might create exception log
 * which is not writable for apache user.
 * Update rights so apache user could always write to log.
 * Create log as apache user would create it unwritable for CLI user.
 */
class ChangeExceptionLogRights implements ShopServiceInterface
{
    /** @var ServiceConfig */
    private $serviceConfig;

    /** @var Filesystem */
    private $fileSystem;

    /** @var String partly path to exception log */
    const EXCEPTION_LOG_PATH = 'log' . DIRECTORY_SEPARATOR . 'oxideshop.log';

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
