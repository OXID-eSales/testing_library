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
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
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
