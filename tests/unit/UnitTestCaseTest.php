<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * Empty non abstract class for testing the unit test case.
 */
class testCase extends \OxidEsales\TestingLibrary\UnitTestCase
{

}

/**
 * Test for the UnitTestCase class.
 * Delegation and setter/getter tests are postponed for now, feel free to write them!
 */
class UnitTestCaseTest extends UnitTestCase
{

    /**
     * Test, that the method getTearDownSqls is empty when nothing is added.
     */
    public function testGetTearDownSqlsReturnsEmptyArrayAfterCreation()
    {
        $unitTestCase = new testCase();

        $this->assertEmpty($unitTestCase->getTeardownSqls());
    }

    /**
     * Test, that the method addTearDownSql adds one sql correct.
     */
    public function testAddTearDownSqlLeadsToTheCorrectSqlArray()
    {
        $unitTestCase = new testCase();

        $sql = 'SELECT * FROM oxarticles;';

        $unitTestCase->addTeardownSql($sql);

        $this->assertSame([$sql], $unitTestCase->getTeardownSqls());
    }

    /**
     * Test, that the method addTearDownSql adds the same sql only once.
     */
    public function testAddTearDownSqlDoesntAddsTheSameSqlTwoTimes()
    {
        $unitTestCase = new testCase();

        $sql = 'SELECT * FROM oxarticles;';

        $unitTestCase->addTeardownSql($sql);
        $unitTestCase->addTeardownSql($sql);

        $this->assertSame([$sql], $unitTestCase->getTeardownSqls());
    }

    /**
     * Test, that the method addTearDownSql adds multiple sqls correct.
     */
    public function testAddTearDownSqlAddsMultipleSqlsCorrect()
    {
        $unitTestCase = new testCase();

        $sqlOne = 'SELECT * FROM oxarticles;';
        $sqlTwo = "INSERT INTO oxarticles(OXID) VALUES('EXAMPLE_OXID');";
        $sqlThree = "UPDATE oxarticles SET OXTITLE='EXAMPLE_TITLE' WHERE OXID='EXAMPLE_OXID';";

        $unitTestCase->addTeardownSql($sqlOne);
        $unitTestCase->addTeardownSql($sqlTwo);
        $unitTestCase->addTeardownSql($sqlThree);
        $unitTestCase->addTeardownSql($sqlTwo);

        $this->assertSame([$sqlOne, $sqlTwo, $sqlThree], $unitTestCase->getTeardownSqls());
    }

    /**
     * Test, that the method getTablesForCleanup returns an empty array after creation.
     */
    public function testGetTableForCleanup()
    {
        $unitTestCase = new testCase();

        $this->assertEmpty($unitTestCase->getTablesForCleanup());
    }

    /**
     * Test, that the method addTablesForCleanup adds one table name correct.
     */
    public function testAddTableForCleanupAddsOneTableNameCorrect()
    {
        $unitTestCase = new testCase();

        $tableName = 'oxcount';

        $unitTestCase->addTableForCleanup($tableName);

        $this->assertNotEmpty($unitTestCase->getTablesForCleanup());
        $this->assertSame([$tableName], $unitTestCase->getTablesForCleanup());
    }

    /**
     * Test, that the method addTablesForCleanup adds one table name not a second time.
     */
    public function testAddTableForCleanupAddsOneTableNameOnlyOnce()
    {
        $unitTestCase = new testCase();

        $tableName = 'oxcount';

        $unitTestCase->addTableForCleanup($tableName);
        $unitTestCase->addTableForCleanup($tableName);

        $this->assertNotEmpty($unitTestCase->getTablesForCleanup());
        $this->assertSame([$tableName], $unitTestCase->getTablesForCleanup());
    }

    /**
     * Test, that the method addTablesForCleanup adds a multi table name and it.
     */
    public function testAddTableForCleanupAddsTheMultiShopTablesCorrect()
    {
        $unitTestCase = new testCase();

        $tableName = 'oxarticles';
        $tableNameToShop = 'oxarticles2shop';

        $unitTestCase->addTableForCleanup($tableName);

        $this->assertNotEmpty($unitTestCase->getTablesForCleanup());
        if ('EE' === $this->getTestConfig()->getShopEdition()) {
            $this->assertSame([$tableName, $tableNameToShop], $unitTestCase->getTablesForCleanup());
        } else {
            $this->assertSame([$tableName], $unitTestCase->getTablesForCleanup());
        }
    }

}
