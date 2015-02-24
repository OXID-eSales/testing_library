<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
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
