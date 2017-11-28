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
interface DatabaseRestorerInterface
{
    /**
     * Creates a dump of the current database, stored in the file '/tmp/tmp_db_dump'
     * the dump includes the data and sql insert statements
     *
     * @param string $dumpName Only used during database preparation.
     *
     * @throws Exception
     */
    public function dumpDB($dumpName = 'test');

    /**
     * Checks which tables of the db changed and then restores these tables.
     * Uses dump file '/tmp/tmp_db_dump' for comparison and restoring.
     *
     * @param string $dumpName Only used during database preparation.
     */
    public function restoreDB($dumpName = 'test');

    /**
     * Restores table records
     *
     * @param string $table           Table to restore
     * @param bool   $restoreColumns whether to check and restore table columns
     *
     * @return bool whether table had changes
     */
    public function restoreTable($table, $restoreColumns = false);
}
