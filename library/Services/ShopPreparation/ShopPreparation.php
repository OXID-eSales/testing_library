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
namespace OxidEsales\TestingLibrary\Services\ShopPreparation;

use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerFactory;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerInterface;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;
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
        $configFile = \oxRegistry::get('oxConfigFile');
        $this->databaseHandler = new DatabaseHandler($configFile, $config->getTempDirectory());

        $factory = new DatabaseRestorerFactory();
        $this->databaseRestorer = $factory->createRestorer('DatabaseRestorerToFile');
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
