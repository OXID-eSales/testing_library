<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopPreparation;

use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerFactory;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerInterface;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerToFile;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Shop constructor class for modifying shop environment during testing
 * Class ShopConstructor
 */
class ShopPreparation implements ShopServiceInterface
{
    /** @var DatabaseHandler Database communicator object */
    private $databaseHandler = null;

    /** @var DatabaseRestorerInterface Database communicator object */
    private $databaseRestorer = null;

    /**
     * Initiates class dependencies.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $configFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $this->databaseHandler = new DatabaseHandler($configFile, $config->getTempDirectory());

        $factory = new DatabaseRestorerFactory();
        $this->databaseRestorer = $factory->createRestorer(DatabaseRestorerToFile::class);
    }

    /**
     * Handles request parameters.
     *
     * @param Request $request
     */
    public function init($request)
    {
        if ($file = $request->getUploadedFile('importSql')) {
            $databaseHandler = $this->getDatabaseHandler();
            $databaseHandler->import($file);
        }

        if ($request->getParameter('dumpDB')) {
            $databaseRestorer = $this->getDatabaseRestorer();
            $databaseRestorer->dumpDB($request->getParameter('dump-prefix'));
        }

        if ($request->getParameter('restoreDB')) {
            $databaseRestorer = $this->getDatabaseRestorer();
            $databaseRestorer->restoreDB($request->getParameter('dump-prefix'));
        }
    }

    /**
     * @return DatabaseHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->databaseHandler;
    }

    /**
     * @return DatabaseRestorerInterface
     */
    protected function getDatabaseRestorer()
    {
        return $this->databaseRestorer;
    }
}
