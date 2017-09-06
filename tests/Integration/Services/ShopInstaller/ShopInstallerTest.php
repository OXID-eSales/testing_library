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
 * @copyright (C) OXID eSales AG 2003-2017
 */

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\TestingLibrary\ServiceCaller;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;

use OxidEsales\TestingLibrary\TestConfig;

class ShopInstallerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testShopInstaller()
    {
        $this->checkBeforeInstall();

        $this->dropOxDiscountView();
        $this->assertOxDiscountViewNotExists();

        $serviceCaller = new ServiceCaller(new TestConfig());
        try {
            $serviceCaller->callService('ShopInstaller');
        } catch (\Exception $e) {
            exit("Failed to install shop with message:" . $e->getMessage());
        }

        $this->checkAfterInstall();
    }

    protected function checkBeforeInstall()
    {
        $shopConfig = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $dbHandler = new DatabaseHandler($shopConfig);

        $this->adjustTemplateBlocksOxModuleColumn();

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $dbHandler->getDbName() . "'";
        $result = DatabaseProvider::getDb()->getOne($sql);

        $this->assertNotEmpty($result);

        $this->assertOxModuleColumnHasMaxLength(32);
        $this->assertOxDiscountViewExists();
    }

    protected function checkAfterInstall()
    {
        $this->assertOxModuleColumnHasMaxLength(100);
        $this->assertOxDiscountViewExists();
    }

    /**
     * @param int $expectedMaxLength
     */
    private function assertOxModuleColumnHasMaxLength($expectedMaxLength)
    {
        $columnInformation = $this->getOxModuleColumnInformation();

        $this->assertEquals($expectedMaxLength, $columnInformation->max_length);
    }

    protected function getOxModuleColumnInformation()
    {
        $database = DatabaseProvider::getDb();
        $columns = $database->metaColumns('oxtplblocks');

        foreach($columns as $column) {
            if ($column->name === 'OXMODULE') {

                return $column;
            }
        }
    }

    protected function adjustTemplateBlocksOxModuleColumn()
    {
        $database = DatabaseProvider::getDb();
        $sql = "ALTER TABLE `oxtplblocks` 
          CHANGE `OXMODULE` `OXMODULE` char(32) 
          character set latin1 collate latin1_general_ci NOT NULL 
          COMMENT 'Module, which uses this template';";
        $database->execute($sql);
    }

    protected function dropOxDiscountView()
    {
        if ($this->existsOxDiscountView()) {
            $database = DatabaseProvider::getDb();

            $database->execute("DROP VIEW oxv_oxdiscount");
        }
    }

    protected function assertOxDiscountViewExists()
    {
        $this->assertTrue($this->existsOxDiscountView(), 'Expected view oxv_oxdiscount does not exist!');
    }

    protected function assertOxDiscountViewNotExists()
    {
        $this->assertFalse($this->existsOxDiscountView(), 'Expected that view oxv_oxdiscount does not exist, but it does!');
    }

    protected function existsOxDiscountView()
    {
        $sql = "SELECT count(*) FROM INFORMATION_SCHEMA.VIEWS WHERE	TABLE_NAME = 'oxv_oxdiscount'";

        return '1' == DatabaseProvider::getDb()->getOne($sql);
    }
}
