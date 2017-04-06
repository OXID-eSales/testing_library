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
namespace OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer;

use oxDb;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;


/**
 * Database maintenance class responsible complete for backuping and restoration of test database.
 */
class DatabaseRestorerToFile implements DatabaseRestorerInterface
{
    /** @var array Dump of the original db */
    private $checksum = null;

    /** @var string Directory, where to store dumped database files. */
    private $baseDumpDirectory = '/tmp/oxid_test_library/db_restore/';

    /** @var string Dump name */
    private $dumpName = 'test';

    /** @var DatabaseHandler */
    private $databaseHandler = null;

    /**
     * Fulfils requirements.
     */
    public function __construct()
    {
        $configFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $this->databaseHandler = new DatabaseHandler($configFile);
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

        foreach ($tables as $table) {
            $databaseHandler = $this->getDatabaseHandler();
            $directory = $this->getDumpDirectory();
            $databaseHandler->export($directory . '/' . $table . '.sql', array($table));
        }
        $this->saveChecksum($this->getTableChecksum($tables));
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

        $database = oxDb::getDb();
        if ($database->getOne("SHOW TABLES LIKE '$table'")) {
            $database->execute("DROP TABLE `$table`");
        }

        $databaseHandler = $this->getDatabaseHandler();
        $directory = $this->getDumpDirectory();
        $databaseHandler->import($directory . '/' . $table . '.sql');
    }

    /**
     * Drops table
     *
     * @param string $sTable
     */
    private function dropTable($sTable)
    {
        $oDB = oxDb::getDb();
        $oDB->execute("DROP TABLE `$sTable`");
    }

    /**
     * Returns database dump data
     *
     * @return array
     */
    private function getDumpChecksum()
    {
        $dumpName = $this->getDumpName();
        $checksum = $this->getChecksum();

        return array_key_exists($dumpName, $checksum)? $checksum[$dumpName] : array();
    }

    /**
     * @return array
     */
    protected function getChecksum()
    {
        if (!$this->checksum) {
            $this->checksum = array();
            $dumpDirectory = $this->getDumpDirectory();
            if (file_exists($dumpDirectory.'/checksums.txt')) {
                $this->checksum = unserialize(file_get_contents($dumpDirectory.'/checksums.txt'));
            }
        }

        return $this->checksum;
    }

    /**
     * Saves tables checksum.
     *
     * @param array $dumpChecksum
     */
    protected function saveChecksum($dumpChecksum)
    {
        $dumpName = $this->getDumpName();
        $dumpDirectory = $this->getDumpDirectory();
        $allChecksum = $this->getChecksum();
        $allChecksum[$dumpName] = $dumpChecksum;
        $this->checksum = $allChecksum;

        file_put_contents($dumpDirectory.'/checksums.txt', serialize($allChecksum));
    }

    /**
     * Returns given tables checksum values.
     *
     * @param array $tables Tables for which checksum will be generated.
     *
     * @return array
     */
    private function getTableChecksum($tables)
    {
        $tables = is_array($tables) ? $tables : array($tables);
        $database = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $select = 'CHECKSUM TABLE ' . implode(", ", $tables);
        $results = $database->getAll($select);

        $sDbName = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class)->getVar('dbName');
        $checksum = array();
        foreach ($results as $result) {
            $table = str_replace($sDbName . '.', '', $result['Table']);
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
        $database = oxDb::getDb(oxDb::FETCH_MODE_NUM);
        $tables = $database->getCol("SHOW TABLES");

        foreach ($tables as $key => $table) {
            if (strpos($table, 'oxv_') === 0) {
                unset($tables[$key]);
            }
        }
        return $tables;
    }

    /**
     * Create dump file name
     *
     * @return string
     */
    protected function getDumpDirectory()
    {
        $dumpName = $this->getDumpName();
        $databaseHandler = $this->getDatabaseHandler();
        $directory = $this->getBaseDumpDirectory() . '/' . $databaseHandler->getDbName() . '/' . $dumpName . '/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    /**
     * Return temporary folder path
     *
     * @return string
     */
    protected function getBaseDumpDirectory()
    {
        return $this->baseDumpDirectory;
    }

    /**
     * @return DatabaseHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->databaseHandler;
    }
}
