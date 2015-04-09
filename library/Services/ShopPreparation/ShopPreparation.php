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

require_once LIBRARY_PATH .'/FileUploader.php';
require_once LIBRARY_PATH .'/DbHandler.php';

/**
 * Shop constructor class for modifying shop environment during testing
 * Class ShopConstructor
 */
class ShopPreparation implements ShopServiceInterface
{
    /** @var DbHandler Database communicator object */
    private $_dbHandler = null;

    /**
     * Initiates class dependencies.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        include_once $config->getShopDirectory() . "core/oxconfigfile.php";
        $configFile = new oxConfigFile($config->getShopDirectory() . "config.inc.php");
        $this->_dbHandler = new DbHandler($configFile);
        $this->_dbHandler->setTemporaryFolder($config->getTempDirectory());
    }

    /**
     * Handles request parameters.
     *
     * @param Request $request
     *
     * @return null
     */
    public function init($request)
    {
        if ($file = $request->getUploadedFile('importSql')) {
            $oDbHandler = $this->getDbHandler();
            $oDbHandler->import($file);
        }

        if ($request->getParameter('dumpDB')) {
            $oDbHandler = $this->getDbHandler();
            $oDbHandler->dumpDB($request->getParameter('dump-prefix'));
        }

        if ($request->getParameter('restoreDB')) {
            $oDbHandler = $this->getDbHandler();
            $oDbHandler->restoreDB($request->getParameter('dump-prefix'));
        }
    }

    /**
     * Returns Database handler object.
     *
     * @return DbHandler
     */
    protected function getDbHandler()
    {
        return $this->_dbHandler;
    }
}
