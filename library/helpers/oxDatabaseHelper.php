<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;

class oxDatabaseHelper
{
    /** @var DatabaseInterface The database to use */
    protected $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
        $this->database->forceMasterConnection();
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     *
     * @return object
     */
    public function getFieldInformation($tableName, $fieldName)
    {
        $columns = $this->database->metaColumns($tableName);

        foreach($columns as $column) {
            if ($column->name === $fieldName) {

                return $column;
            }
        }

        return null;
    }

    /**
     * @param string $tableName
     */
    public function dropView($tableName)
    {
        if ($this->existsView($tableName)) {
            $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
            $tableNameView = $generator->getViewName($tableName, 0);

            $this->database->execute("DROP VIEW " . $this->database->quoteIdentifier($tableNameView));
        }
    }

    /**
     * @param string $tableName
     *
     * @return bool Does the view with the given name exists?
     */
    public function existsView($tableName)
    {
        $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
        $tableNameView = $generator->getViewName($tableName, 0);
        $sql = "SHOW TABLES LIKE '$tableNameView'";

        return $tableNameView === $this->database->getOne($sql);
    }

    /**
     * @param string $tableName The name of the table we want to assure to exist.
     *
     * @return bool Does the database table with the given name exists?
     */
    public function existsTable($tableName)
    {
        $sql = "SELECT COUNT(TABLE_NAME) FROM information_schema.TABLES WHERE TABLE_NAME = '$tableName'";

        $count = $this->database->getOne($sql);

        return $count > 0;
    }

    public function adjustTemplateBlocksOxModuleColumn()
    {
        $sql = "ALTER TABLE `oxtplblocks` 
          CHANGE `OXMODULE` `OXMODULE` char(32) 
          character set latin1 collate latin1_general_ci NOT NULL 
          COMMENT 'Module, which uses this template';";

        $this->database->execute($sql);
    }

    public function getDataBaseTables()
    {
        $shopConfigFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $databaseHandler = new DatabaseHandler($shopConfigFile);

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $databaseHandler->getDbName() . "'";

        return $this->database->getAll($sql);
    }
}
