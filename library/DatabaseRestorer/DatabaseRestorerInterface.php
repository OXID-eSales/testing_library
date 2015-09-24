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
namespace OxidEsales\TestingLibrary\DatabaseRestorer;

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
