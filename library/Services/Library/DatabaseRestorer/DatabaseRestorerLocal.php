<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer;

use OxidEsales\TestingLibrary\Services\Library\FileHandler;


/**
 * Database maintenance class responsible complete for backuping and restoration of test database.
 */
class DatabaseRestorerLocal implements DatabaseRestorerInterface
{
    /** @var string Temp directory, where to store database dump */
    private $tempDirectory = '/tmp/';

    /** @var string Dump file path */
    private $tmpFilePath = null;

    /** @var array Dump of the original db */
    private $checksum = array();

    /** @var string Dump name */
    private $dumpName = 'test';

    /** @var FileHandler */
    private $fileHandler = null;

    /**
     * Sets class dependencies.
     */
    public function __construct()
    {
        $this->fileHandler = new FileHandler();
    }

    /**
     * Sets which dump should be used for restoration.
     *
     * @param string $dumpName Only used during database preparation.
     */
    public function setDumpName($dumpName)
    {
        $this->dumpName = $dumpName;
    }

    /**
     * Returns dump name to use for restoration.
     *
     * @return string
     */
    public function getDumpName()
    {
        return $this->dumpName;
    }

    /**
     * Create database tables dump for active database
     *
     * @param string $dumpName
     */
    public function dumpDB($dumpName = 'test')
    {
        $this->setDumpName($dumpName);
        $tables = $this->getDbTables();
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();

        foreach ($tables as $table) {
            $file = $this->getDumpFolderPath() . '/' . $table . '_dump.sql';
            if (file_exists($file)) {
                unlink($file);
            }

            $query = "SELECT * INTO OUTFILE '" . $file . "' FROM $table";
            $db->execute($query);
        }

        $this->checksum[$dumpName] = $this->getTableChecksum($tables);
    }

    /**
     * Checks which tables of the db changed and then restores these tables.
     * Uses dump file '/tmp/tmp_db_dump' for comparison and restoring.
     *
     * @param string $dumpName
     */
    public function restoreDB($dumpName = 'test')
    {
        $this->setDumpName($dumpName);
        $tables = $this->getDbTables();

        $dumpChecksum = $this->getDumpChecksum();
        $dumpTables = array_keys($dumpChecksum);

        foreach ($tables as $table) {
            if (!in_array($table, $dumpTables)) {
                $this->dropTable($table);
            } else {
                $this->restoreTable($table);
            }
        }

        $missingTables = array_diff($dumpTables, $tables);
        foreach ($missingTables as $table) {
            $this->restoreTable($table);
        }
        $this->setDumpName('test');
    }

    /**
     * Restores table records.
     *
     * @param string $table          Table to restore.
     * @param bool   $restoreColumns Whether to restore table columns.
     *
     */
    public function restoreTable($table, $restoreColumns = false)
    {
        $dumpChecksum = $this->getDumpChecksum();
        $checksum = $this->getTableChecksum($table);
        if ($checksum[$table] === $dumpChecksum[$table]) {
            return;
        }

        $file = $this->getDumpFolderPath() .'/'. $table ."_dump.sql";

        if (file_exists($file)) {
            $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
            $database->execute("TRUNCATE TABLE `$table`");

            $query = "LOAD DATA INFILE '$file' INTO TABLE `$table`";
            $database->execute($query);
        }
    }

    /**
     * Drops table
     *
     * @param string $table
     */
    private function dropTable($table)
    {
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
        $database->execute("DROP TABLE `$table`");
    }

    /**
     * Returns dump file path
     *
     * @return string
     */
    private function getDumpFolderPath()
    {
        if (is_null($this->tmpFilePath)) {
            $dumpName = $this->getDumpName();
            $databaseName = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbName');
            $this->tmpFilePath = $this->tempDirectory . '/' . $databaseName . '_dbdump/'. $dumpName .'/';
            $this->getFileHandler()->createDirectory($this->tmpFilePath);
        }

        return $this->tmpFilePath;
    }

    /**
     * Returns database dump data
     *
     * @return array
     */
    private function getDumpChecksum()
    {
        $dumpName = $this->getDumpName();
        return $this->checksum[$dumpName];
    }

    /**
     * Returns given tables checksum values.
     *
     * @param array|string $tables Tables for which checksum will be generated.
     *
     * @return array
     */
    private function getTableChecksum($tables)
    {
        $tables = is_array($tables) ? $tables : array($tables);
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
        $query = 'CHECKSUM TABLE ' . implode(", ", $tables);
        $results = $database->getAll($query);

        $databaseName = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbName');
        $checksum = array();
        foreach ($results as $result) {
            $table = str_replace($databaseName . '.', '', $result['Table']);
            $checksum[$table] = $result['Checksum'];
        }

        return $checksum;
    }

    /**
     * Returns database tables, excluding views
     *
     * @return array Array of tables in the database excluding views.
     */
    private function getDbTables()
    {
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_NUM);
        $tables = $database->getCol("SHOW TABLES");

        foreach ($tables as $key => $table) {
            if (strpos($table, 'oxv_') === 0) {
                unset($tables[$key]);
            }
        }
        return $tables;
    }

    /**
     * @return FileHandler
     */
    protected function getFileHandler()
    {
        return $this->fileHandler;
    }
}
