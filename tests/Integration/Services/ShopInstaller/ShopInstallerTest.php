<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\TestingLibrary\ServiceCaller;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;
use OxidEsales\TestingLibrary\TestConfig;

require_once TEST_LIBRARY_HELPERS_PATH . 'oxDatabaseHelper.php';

class ShopInstallerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    const DEFAULT_OXMODULE_COLUMN_MAX_LENGTH = 32;
    const CHANGED_OXMODULE_COLUMN_MAX_LENGTH = 100;

    public function testShopInstallerCallsMigrationsAndRegeneratesViews()
    {
        $this->checkBeforeInstall();

        // to be able to assert afterwards, that the views generation was called, we delete one view here
        $this->dropOxDiscountView();

        try {
            $serviceCaller = new ServiceCaller(new TestConfig());

            $serviceCaller->callService('ShopInstaller');
        } catch (\Exception $e) {
            exit("Failed to install shop with message:" . $e->getMessage());
        }

        $this->checkAfterInstall();
    }

    /**
     * To be able to assure, that the ShopInstall service call worked correct, we check before, if everything is well.
     */
    protected function checkBeforeInstall()
    {
        $databaseHelper = new oxDatabaseHelper(DatabaseProvider::getDb());
        $databaseHelper->adjustTemplateBlocksOxModuleColumn();

        $this->assertThereExistsAtLeastOneDatabaseTable();
        $this->assertOxModuleColumnHasMaxLength(self::DEFAULT_OXMODULE_COLUMN_MAX_LENGTH);
        $this->assureGenerateViewsWasCalled();
    }

    /**
     * To assure, that the ShopInstall service call worked correct, we check, that
     *  - the views are regenerated
     *  - the migrations where called
     */
    protected function checkAfterInstall()
    {
        $this->assureMigrationWasCalled();
        $this->assureGenerateViewsWasCalled();
    }

    /**
     * @param int $expectedMaxLength
     */
    private function assertOxModuleColumnHasMaxLength($expectedMaxLength)
    {
        $databaseHelper = new oxDatabaseHelper(DatabaseProvider::getDb());

        $columnInformation = $databaseHelper->getFieldInformation('oxtplblocks', 'OXMODULE');

        $this->assertEquals($expectedMaxLength, $columnInformation->max_length);
    }

    protected function dropOxDiscountView()
    {
        $databaseHelper = new oxDatabaseHelper(DatabaseProvider::getDb());

        $databaseHelper->dropView('oxdiscount');

        $this->assertViewNotExists('oxdiscount');
    }

    private function assureMigrationWasCalled()
    {
        $this->assertOxModuleColumnHasMaxLength(self::CHANGED_OXMODULE_COLUMN_MAX_LENGTH);
    }

    protected function assureGenerateViewsWasCalled()
    {
        $this->assertViewExists('oxdiscount');
    }

    protected function assertThereExistsAtLeastOneDatabaseTable()
    {
        $databaseHelper = new oxDatabaseHelper(DatabaseProvider::getDb());

        $this->assertNotEmpty($databaseHelper->getDataBaseTables());
    }
}
