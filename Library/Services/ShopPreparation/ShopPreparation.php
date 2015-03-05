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

require_once LIBRARY_PATH.'/FileUploader.php';
require_once 'DbHandler.php';

/**
 * Shop constructor class for modifying shop environment during testing
 * Class ShopConstructor
 */
class ShopPreparation implements ShopServiceInterface
{
    /** @var DbHandler Database communicator object */
    private $_dbHandler = null;

    /**
     * Handles request parameters.
     */
    public function init()
    {
        $request = new Request();

        if ($request->getUploadedFile('importSql')) {
            $this->_importSqlFromUploadedFile();
        }

        if ($request->getParameter('dumpDB')) {
            $oDbHandler = $this->_getDbHandler();
            $oDbHandler->dumpDB($request->getParameter('dump-prefix'));
        }

        if ($request->getParameter('restoreDB')) {
            $oDbHandler = $this->_getDbHandler();
            $oDbHandler->restoreDB($request->getParameter('dump-prefix'));
        }
    }

    /**
     * Imports uploaded file with containing sql to shop.
     */
    private function _importSqlFromUploadedFile()
    {
        $oFileUploader = new FileUploader();
        $sFilePath = TEMP_PATH.'/import.sql';
        $oFileUploader->uploadFile('importSql', $sFilePath);

        $oDbHandler = $this->_getDbHandler();
        $oDbHandler->import($sFilePath);
    }

    /**
     * Returns Database handler object.
     *
     * @return DbHandler
     */
    private function _getDbHandler()
    {
        if (!$this->_dbHandler) {
            $this->_dbHandler = new DbHandler();
            $this->_dbHandler->setTemporaryFolder(TEMP_PATH);
        }

        return $this->_dbHandler;
    }
}
