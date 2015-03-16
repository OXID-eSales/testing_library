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

require_once __DIR__.'/dbRestoreInterface.php';

/**
 * Database maintenance class responsible complete for backuping and restoration of test database.
 */
class DbRestore implements DbRestoreInterface
{

    /** @var string Temp directory, where to store database dump */
    private $tmpDir = '/tmp/';

    /** @var string Dump file path */
    private $tmpFilePath = null;

    /** @var array Dump of the original db */
    private $dbChecksum = null;

    /** @var string Dump name */
    private $dumpName = 'test';

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
        $db = oxDb::getDb();

        foreach ($tables as $table) {
            $file = $this->getDumpFolderPath() . '/' . $table . '_dump.sql';
            if (file_exists($file)) {
                unlink($file);
            }

            $query = "SELECT * INTO OUTFILE '" . $file . "' FROM $table";
            $db->query($query);
        }

        $this->dbChecksum[$dumpName] = $this->getTableChecksum($tables);
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
     * @return null
     */
    public function restoreTable($table, $restoreColumns = false)
    {
        $dumpChecksum = $this->getDumpChecksum();
        $checksum = $this->getTableChecksum($table);
        if ($checksum[$table] === $dumpChecksum[$table]) {
            return;
        }

        $sFile = $this->getDumpFolderPath() .'/'. $table ."_dump.sql";

        if (file_exists($sFile)) {
            $oDb = oxDb::getDb();
            $oDb->query("TRUNCATE TABLE `$table`");

            $sql = "LOAD DATA INFILE '$sFile' INTO TABLE `$table`";
            $oDb->Query($sql);
        }
    }

    /**
     * Drops table
     *
     * @param string $sTable
     */
    private function dropTable($sTable)
    {
        $oDB = oxDb::getDb();
        $oDB->query("DROP TABLE `$sTable`");
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
            $sDbName = oxRegistry::getConfig()->getConfigParam('dbName');
            $this->tmpFilePath = $this->tmpDir . '/' . $sDbName . '_dbdump/'. $dumpName .'/';
            if (!file_exists($this->tmpFilePath)) {
                mkdir($this->tmpFilePath, 0777, true);
                chmod($this->tmpFilePath, 0777);
            }
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
        return $this->dbChecksum[$dumpName];
    }

    /**
     * Returns given tables checksum values.
     *
     * @param array $aTables Tables for which checksum will be generated.
     *
     * @return array
     */
    private function getTableChecksum($aTables)
    {
        $aTables = is_array($aTables) ? $aTables : array($aTables);
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sSelect = 'CHECKSUM TABLE ' . implode(", ", $aTables);
        $aResults = $oDb->getArray($sSelect);

        $sDbName = oxRegistry::getConfig()->getConfigParam('dbName');
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
     * @return array Array of tables in the database excluding views.
     */
    private function getDbTables()
    {
        $oDB = oxDb::getDb(oxDb::FETCH_MODE_NUM);
        $aTables = $oDB->getCol("SHOW TABLES");

        foreach ($aTables as $iKey => $sTable) {
            if (strpos($sTable, 'oxv_') === 0) {
                unset($aTables[$iKey]);
            }
        }
        return $aTables;
    }
}
