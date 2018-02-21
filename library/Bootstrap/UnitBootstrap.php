<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Bootstrap {

    class UnitBootstrap extends BootstrapBase
    {
        /** @var int Whether to add demo data. */
        protected $addDemoData = 0;

        /**
         * Initiates shop before testing.
         * Loads additional.inc and OxidTestCase classes.
         */
        public function init()
        {
            parent::init();
            $this->initializeConfig();

            $currentTestSuite = $this->getTestConfig()->getCurrentTestSuite();
            if (file_exists($currentTestSuite .'/additional.inc.php')) {
                include_once $currentTestSuite .'/additional.inc.php';
                // There is a need to reinitialize config, otherwise configs from SQL file which can be imported via
                // additional.inc.php will not be taken.
                $this->initializeConfig();
            }

            define('TEST_PREPARATION_FINISHED', true);
        }

        /**
         * Prepare shop configuration. Force UTF8 mode, compile directory and database name to be used during testing.
         */
        public function prepareShop()
        {
            parent::prepareShop();

            $shopConfig = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
            $shopConfig->setVar('iUtfMode', 1);
            if ($testDatabase = $this->getTestConfig()->getTestDatabaseName()) {
                $shopConfig->setVar('dbName', $testDatabase);
            }
        }

        /**
         * Forces configuration values from oxConfigFile object to oxConfig.
         */
        public function initializeConfig()
        {
            $config = \OxidEsales\Eshop\Core\Registry::getConfig();
            $configFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
            $config->reinitialize();
            $config->setConfigParam('iUtfMode', $configFile->getVar('iUtfMode'));
            $config->setConfigParam('dbName', $configFile->getVar('dbName'));
        }
    }
}

namespace {

    /**
     * @deprecated Use TestConfig::getCurrentTestSuite() or TestConfig::getTempDirectory().
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

    /**
     * Returns framework base path.
     * Overwrites original method so that it would be possible to mock shop directory during testing.
     *
     * @return string
     */
    function getShopBasePath()
    {
        $shopDirectory = null;
        if (defined('TEST_PREPARATION_FINISHED')) {
            $config = \OxidEsales\Eshop\Core\Registry::getConfig();
            $shopDirectory = $config->getConfigParam('sShopDir');
        }
        return rtrim($shopDirectory ?: OX_BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
