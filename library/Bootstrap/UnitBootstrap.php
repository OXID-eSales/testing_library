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

namespace OxidEsales\TestingLibrary\Bootstrap {

    use OxidEsales\TestingLibrary\FileCopier;

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

            require_once TEST_LIBRARY_PATH .'OxidTestCase.php';

            define('TEST_PREPARATION_FINISHED', true);
        }

        /**
         * Prepare shop configuration. Force UTF8 mode, compile directory and database name to be used during testing.
         */
        public function prepareShop()
        {
            parent::prepareShop();

            $shopConfig = \oxRegistry::get("oxConfigFile");
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
            $config = \oxRegistry::getConfig();
            $configFile = \oxRegistry::get("oxConfigFile");
            $config->reinitialize();
            $config->setConfigParam('iUtfMode', $configFile->getVar('iUtfMode'));
            $config->setConfigParam('dbName', $configFile->getVar('dbName'));
        }
    }
}

namespace {

    use OxidEsales\Eshop\Core\Registry;

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
            $config = Registry::getConfig();
            $shopDirectory = $config->getConfigParam('sShopDir');
        }
        return rtrim($shopDirectory ?: OX_BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
