<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer;

use Exception;

/**
 * Database maintenance class responsible complete for backuping and restoration of test database.
 */
class DatabaseRestorer implements DatabaseRestorerInterface
{
    /** @var string Dump name to use for database restoration */
    private $dumpName = 'test';

    /** @var array Dump of the original db */
    private $dbDump = null;

    /**
     * Creates a dump of the current database, stored in the file '/tmp/tmp_db_dump'
     * the dump includes the data and sql insert statements
     *
     * @param string $dumpName Only used during database preparation.
     *
     * @throws Exception
     */
    public function dumpDB($dumpName = 'test')
    {
        $tables = $this->getDbTables();

        if (empty($tables)) {
            $dbName = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbName');
            throw new Exception("no tables on '$dbName'' database");
        }

        $columns = $this->getTableColumns($tables);
        $data = $this->getTableData($tables);
        $checksum = $this->getTableChecksum($tables);

        $this->dbDump[$dumpName] = array('columns' => $columns, 'data' => $data, 'checksum' => $checksum);
    }

    /**
     * Checks which tables of the db changed and then restores these tables.
     * Uses dump file '/tmp/tmp_db_dump' for comparison and restoring.
     *
     * @param string $dumpName Only used during database preparation.
     */
    public function restoreDB($dumpName = 'test')
    {
        $this->setDumpName($dumpName);

        $data = $this->getDumpData();
        $tables = $this->getDbTables();

        foreach ($tables as $table) {
            if (!isset($data[$table])) {
                $this->dropTable($table);
            } else {
                $this->restoreTable($table);
            }
        }
        $this->setDumpName('test');
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
     * Restores table records
     *
     * @param string $table           Table to restore
     * @param bool   $restoreColumns whether to check and restore table columns
     *
     * @return bool whether table had changes
     */
    public function restoreTable($table, $restoreColumns = false)
    {
        $dumpChecksum = $this->getDumpChecksum();
        $currentChecksum = $this->getTableChecksum($table);

        if ($currentChecksum[$table] === $dumpChecksum[$table]) {
            return false;
        }

        if ($restoreColumns) {
            $this->restoreColumns($table);
        }

        $this->resetTable($table);
        return true;
    }

    /**
     * Drops all table records and adds them back from dump
     *
     * @param string $table
     *
     */
    public function resetTable($table)
    {
        $data = $this->getDumpData();

        $this->executeQuery("TRUNCATE TABLE `$table`");
        if (isset($data[$table]["_sql_"])) {
            $this->executeQuery($data[$table]["_sql_"]);
        }
    }

    /**
     * Returns columns array for given tables.
     *
     * @param array $tables
     * @return array
     */
    private function getTableColumns($tables)
    {
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);

        $columns = array();
        foreach ($tables as $table) {
            $tmp = $database->getAll("SHOW COLUMNS FROM `$table`");
            foreach ($tmp as $sub) {
                $key = $sub['Field'];
                unset($sub['Field']);
                $columns[$table][$key] = $sub;
            }
        }

        return $columns;
    }

    /**
     * Returns data for given tables.
     *
     * @param array $tables
     * @return array
     */
    private function getTableData($tables)
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);

        $data = array();
        foreach ($tables as $table) {
            $data[$table] = array();

            $result = $db->select("SELECT * FROM `${table}`");
            if ($result && $result->count() > 0) {

                $rows = array();
                while (!$result->EOF) {
                    $rows[] = $result->fields;

                    $result->fetchRow();
                }
                $data[$table]["_sql_"] = $this->getInsertString($rows, $table);
            }
        }

        return $data;
    }

    /**
     * Drops table
     *
     * @param string $sTable
     */
    private function dropTable($sTable)
    {
        $this->executeQuery("DROP TABLE `$sTable`");
    }

    /**
     * Restores table columns (adds or removes columns)N
     *
     * @param string $sTable
     */
    private function restoreColumns($sTable)
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);

        $currentColumns = $db->getAll("SHOW COLUMNS FROM `$sTable`", 'Field');
        $dumpColumns = $this->getDumpColumns();
        $dumpColumns = $dumpColumns[$sTable];

        $excessColumns = array_diff($currentColumns, $dumpColumns);

        if (!empty($excessColumns)) {
            $sSQL = "ALTER TABLE $sTable DROP COLUMN (".implode(', ', $excessColumns).")";
            $this->executeQuery($sSQL);
        }
    }

    /**
     * executes given query.
     *
     * @param string $sQuery
     */
    private function executeQuery($sQuery)
    {
        $oDB = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
        $oDB->execute($sQuery);
    }

    /**
     * Returns database dump data
     *
     * @return array
     */
    private function getDumpData()
    {
        $dumpName = $this->getDumpName();
        return $this->dbDump[$dumpName]['data'];
    }

    /**
     * Returns database dump columns
     *
     * @return array
     */
    private function getDumpColumns()
    {
        $dumpName = $this->getDumpName();
        return $this->dbDump[$dumpName]['columns'];
    }

    /**
     * Returns database dump columns
     *
     * @return array
     */
    private function getDumpChecksum()
    {
        $dumpName = $this->getDumpName();
        return $this->dbDump[$dumpName]['checksum'];
    }

    /**
     * Creates a insert string to insert the given row into to given table
     *
     * @param array  $rows  a array of the current row in the db
     * @param string $table the name of the current table
     *
     * @return string a sql insert string for the given row
     */
    private function getInsertString($rows, $table)
    {
        $columns = array();
        $values = array();
        foreach ($rows as $row) {
            if (empty($columns)) {
                $columns = array_keys($row);
            }
            $rowValues = array();
            foreach ($row as $entry) {
                $entry = is_null($entry) ? "NULL" : \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC)->quote($entry);
                $rowValues[] = $entry;
            }
            $values[] = "(". implode(", ", $rowValues).")";
        }

        $query = "INSERT INTO $table ";
        $query .= "(`".implode("`, `", $columns)."`) VALUES ".implode(", ", $values);

        return $query;
    }

    /**
     * Converts a string to UTF format.
     *
     * @param array|string $aTables
     *
     * @return array
     */
    private function getTableChecksum($aTables)
    {
        $aTables = is_array($aTables) ? $aTables : array($aTables);
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
        $sSelect = 'CHECKSUM TABLE `' . implode("`, `", $aTables) . '`';
        $aResults = $oDb->getAll($sSelect);

        $sDbName = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbName');
        $aChecksum = array();
        foreach ($aResults as $aResult) {
            $sTable = str_replace($sDbName . '.', '', $aResult['Table']);
            $aChecksum[$sTable] = $aResult['Checksum'];
        }

        return $aChecksum;
    }

    /**
     * Returns database tables, excluding views
     *
     * @return array
     */
    private function getDbTables()
    {
        $oDB = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_NUM);
        $aTables = $oDB->getCol("SHOW TABLES");

        foreach ($aTables as $iKey => $sTable) {
            if (strpos($sTable, 'oxv_') === 0) {
                unset($aTables[$iKey]);
            }
        }

        return $aTables;
    }
}
