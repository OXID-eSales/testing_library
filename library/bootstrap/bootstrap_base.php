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

define('OXID_PHP_UNIT', true);

require_once TEST_LIBRARY_PATH.'oxTestConfig.php';
require_once TEST_LIBRARY_PATH.'oxServiceCaller.php';
require_once TEST_LIBRARY_PATH.'oxFileCopier.php';
require_once TEST_LIBRARY_PATH .'test_utils.php';

class Bootstrap
{
    /** @var oxTestConfig */
    private $testConfig;

    /** @var int Whether to add demo data when installing the shop. */
    protected $addDemoData = 1;

    /**
     * Initiates class dependencies.
     */
    public function __construct()
    {
        $this->testConfig = new oxTestConfig();
    }

    /**
     * Prepares tests environment.
     */
    public function init()
    {
        $testConfig = $this->getTestConfig();

        $this->copyServices();

        if ($testConfig->getTempDirectory()) {
            $fileCopier = new oxFileCopier();
            $fileCopier->createEmptyDirectory($testConfig->getTempDirectory());
        }

        if ($testConfig->shouldRestoreShopAfterTestsSuite()) {
            $this->registerResetDbAfterSuite();
        }

        if ($testConfig->shouldInstallShop()) {
            $this->installShop();
        }

        $this->setGlobalConstants();

        $this->setGlobalConstants();

        $this->prepareShopModObjects();
    }

    /**
     * Returns tests config.
     *
     * @return oxTestConfig
     */
    public function getTestConfig()
    {
        return $this->testConfig;
    }

    /**
     * Copies services to shop.
     */
    protected function copyServices()
    {
        $config = $this->getTestConfig();
        $fileCopier = new oxFileCopier();
        $target = $config->getRemoteDirectory() ? $config->getRemoteDirectory().'/Services' : $config->getShopPath().'/Services';
        $fileCopier->copyFiles(TEST_LIBRARY_PATH .'Services', $target, true);
    }

    /**
     * Installs the shop.
     *
     * @throws Exception
     */
    protected function installShop()
    {
        $testConfig = $this->getTestConfig();
        $oCurl = new oxTestCurl();
        $oCurl->setUrl($testConfig->getShopUrl() . '/Services/_db.php');
        $oCurl->setParameters(array(
            'serial' => $testConfig->getShopSerial(),
            'addDemoData' => $this->addDemoData,
            'turnOnVarnish' => $testConfig->shouldEnableVarnish(),
            'setupPath' => $testConfig->getShopSetupPath(),
        ));

        $oCurl->execute();
    }

    /**
     * Sets global constants, as these are still used a lot in tests.
     * This is used to maintain backwards compatibility.
     */
    protected function setGlobalConstants()
    {
        $testConfig = $this->getTestConfig();

        if (file_exists($testConfig->getShopPath() . "/_version_define.php")) {
            include_once $testConfig->getShopPath() . "/_version_define.php";
        }

        define('oxPATH', $testConfig->getShopPath());
        define('OX_BASE_PATH', $testConfig->getShopPath());
        define('shopURL', $testConfig->getShopUrl());
        define('oxSHOPID', $testConfig->getShopId());
        define('isSUBSHOP', $testConfig->isSubShop());

        define('CURRENT_TEST_SUITE', $testConfig->getCurrentTestSuite());
    }

    /**
     * Creates original database dump and registers database restoration
     * after the tests suite.
     */
    protected function registerResetDbAfterSuite()
    {
        $serviceCaller = new oxServiceCaller();
        $serviceCaller->setParameter('dumpDB', true);
        $serviceCaller->setParameter('dump-prefix', 'orig_db_dump');
        try {
            $serviceCaller->callService('ShopPreparation', 1);
        } catch (Exception $e) {
            define('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR', true);
        }

        register_shutdown_function(function () {
            if (!defined('RESTORE_SHOP_AFTER_TEST_SUITE_ERROR')) {
                $serviceCaller = new oxServiceCaller();
                $serviceCaller->setParameter('restoreDB', true);
                $serviceCaller->setParameter('dump-prefix', 'orig_db_dump');
                $serviceCaller->callService('ShopPreparation', 1);
            }
        });
    }

    /**
     * Prepares mocked shop objects like oxConfig, oxDb.
     * Includes shop bootstrap.
     */
    protected function prepareShopModObjects()
    {
        $shopPath = $this->getTestConfig()->getShopPath();
        require_once $shopPath .'core/oxfunctions.php';

        $oConfigFile = new oxConfigFile($shopPath . "config.inc.php");
        oxRegistry::set("OxConfigFile", $oConfigFile);
        oxRegistry::set("oxConfig", new oxConfig());

        $oDb = new oxDb();
        $oDb->setConfig($oConfigFile);
        $oLegacyDb = $oDb->getDb();
        oxRegistry::set('oxDb', $oLegacyDb);

        oxRegistry::getConfig();

        require_once TEST_LIBRARY_PATH .'modOxUtilsDate.php';
        require_once $shopPath .'/core/oxutils.php';
        require_once $shopPath .'/core/adodblite/adodb.inc.php';
        require_once $shopPath .'/core/oxsession.php';
        require_once $shopPath .'/core/oxconfig.php';
    }
}

/**
 * @deprecated Use oxTestConfig::getCurrentTestSuite() or oxTestConfig::getTempDirectory().
 *
 * @return string
 */
function getTestsBasePath()
{
    $testsPath = '';
    if (defined('CURRENT_TEST_SUITE')) {
        $testsPath = CURRENT_TEST_SUITE;
    }
    return $testsPath;
}
