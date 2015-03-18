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

use Symfony\Component\Yaml\Yaml;

if (!defined('TEST_LIBRARY_BASE_PATH')) {
    define('TEST_LIBRARY_BASE_PATH', __DIR__ . '/../');
}

class Test_Config
{
    /** @var array */
    private $configuration;

    /** @var string */
    private $shopPath;

    /** @var string Path to vendors directory */
    private $vendorPath;

    /** @var string Shop edition. Either EE, PE or CE. */
    private $shopEdition;

    /** @var string Shop url. */
    private $shopUrl;

    /** @var string Currently running test suite path. */
    private $currentTestSuite;

    /** @var array All defined test suites. */
    private $testSuites;

    /**
     * Initiates configuration from configuration yaml file.
     */
    public function __construct()
    {
        require_once $this->getVendorPath() .'/autoload.php';

        $yaml = Yaml::parse(file_get_contents($this->getConfigFileName()));
        $this->configuration = array_merge($yaml['mandatory_parameters'], $yaml['optional_parameters']);
    }

    /**
     * Returns path to vendors directory.
     *
     * @return string
     */
    public function getVendorPath()
    {
        if (is_null($this->vendorPath)) {
            $vendorPath = TEST_LIBRARY_BASE_PATH . "../../../vendor/";
            if (!file_exists($vendorPath)) {
                $vendorPath = TEST_LIBRARY_BASE_PATH .'/vendor/';
            }
            $this->vendorPath = realpath($vendorPath);
        }

        return $this->vendorPath;
    }

    /**
     * Returns path to shop source directory.
     *
     * @return string
     */
    public function getShopPath()
    {
        if (!$this->shopPath) {
            $this->shopPath = $this->getValue('shop_path', 'SHOP_PATH');
            if (strpos($this->shopPath, '/') !== 0) {
                $this->shopPath = $this->findShopPath($this->shopPath);
            }
        }

        return realpath($this->shopPath) . '/';
    }

    /**
     * Returns remote directory.
     *
     * @return string|null
     */
    public function getRemoteDirectory()
    {
        return $this->getValue('remote_directory', 'REMOTE_DIR');
    }

    /**
     * Returns shop edition
     *
     * @return array|null|string
     */
    public function getShopEdition()
    {
        if (is_null($this->shopEdition)) {
            $shopEdition = $this->getValue('shop_edition', 'SHOP_EDITION');

            if (!$shopEdition) {
                if (defined('OXID_VERSION_EE')) {
                    $shopEdition = OXID_VERSION_EE ? 'EE' : '';
                    $shopEdition = OXID_VERSION_PE_PE ? 'PE' : $shopEdition;
                    $shopEdition = OXID_VERSION_PE_CE ? 'CE' : $shopEdition;
                }
                if (!$shopEdition) {
                    $shopPath = $this->getShopPath();
                    include_once $shopPath . 'core/oxsupercfg.php';
                    include_once $shopPath . 'core/oxconfig.php';
                    $config = new oxConfig();
                    $shopEdition = $config->getEdition();
                }
            }
            $this->shopEdition = strtoupper($shopEdition);
        }

        return $this->shopEdition;
    }

    /**
     * Returns shop id
     *
     * @return int|string
     */
    public function getShopId()
    {
        $shopId = 'oxbaseshop';
        if ($this->getShopEdition() == 'EE') {
            $isSubShop = (bool)$this->getValue('is_subshop', 'IS_SUBSHOP');
            $shopId = $isSubShop ? 2 : 1;
        }

        return $shopId;
    }

    /**
     * Whether tested shop is subshop.
     *
     * @return bool
     */
    public function isSubShop()
    {
        $shopId = $this->getShopId();
        return is_int($shopId) ? $shopId > 1 : false;
    }

    /**
     * Returns shop url.
     *
     * @return string
     */
    public function getShopUrl()
    {
        if (is_null($this->shopUrl)) {
            $sShopUrl = $this->getValue('shop_url', 'SHOP_URL');
            if (!$sShopUrl) {
                $shopPath = $this->getShopPath();
                include_once $shopPath . 'core/oxconfigfile.php';
                $oConfigFile = new oxConfigFile($shopPath . "config.inc.php");
                $sShopUrl = $sShopUrl ? $sShopUrl : $oConfigFile->sShopURL;
            }
            $this->shopUrl = rtrim($sShopUrl, '/') . '/';
        }

        return $this->shopUrl;
    }

    /**
     * Returns shop tests path.
     *
     * @return string|null
     */
    public function getShopTestsPath()
    {
        $testsPath = $this->getValue('shop_tests_path');
        if (strpos($testsPath, '/') !== 0) {
            $testsPath = $this->getShopPath() . $testsPath;
        }

        return realpath($testsPath) . '/';
    }

    /**
     * Returns array of paths to all modules which should be tested.
     *
     * @return array|null
     */
    public function getModulesToTest()
    {
        return explode(',', $this->getValue('modules_path', 'MODULES_PATH'));
    }

    /**
     * Returns modules for activation.
     *
     * @return array
     */
    public function getModulesToActivate()
    {
        $modulesToActivate = array();

        $current = $this->getCurrentTestSuite();
        $modulesDir = $this->getShopPath() .'modules/';
        foreach ($this->getModulesToTest() as $module) {
            $fullPath = rtrim($modulesDir . $module, '/') .'/';
            if (strpos($current, $fullPath) === 0) {
                $modulesToActivate[] = $module;
                break;
            }
        }

        return $modulesToActivate;
    }

    /**
     * Returns path to shop setup.
     *
     * @return string|null
     */
    public function getShopSetupPath()
    {
        return $this->getValue('shop_setup_path', 'SHOP_SETUP_PATH');
    }

    /**
     * Returns database restoration class name.
     *
     * @return string|null
     */
    public function getDatabaseRestorationClass()
    {
        return $this->getValue('database_restoration_class', 'DATABASE_RESTORATION_CLASS');
    }

    /**
     * Returns what serial to use when installing the shop.
     *
     * @return string|null
     */
    public function getShopSerial()
    {
        return $this->getValue('shop_serial', 'TEST_SHOP_SERIAL ');
    }

    /**
     * Whether to install shop before running tests.
     *
     * @return bool|null
     */
    public function shouldInstallShop()
    {
        return (bool)$this->getValue('install_shop', 'INSTALL_SHOP');
    }

    /**
     * Whether to restore shop database after running all the tests.
     *
     * @return bool|null
     */
    public function shouldRestoreShopAfterTestsSuite()
    {
        return (bool)$this->getValue('restore_shop_after_tests_suite');
    }

    /**
     * Returns temp directory for storing tests data.
     *
     * @return bool|null
     */
    public function getTempDirectory()
    {
        return $this->getValue('tmp_path', 'TMP_PATH');
    }

    /**
     * Whether to enable varnish when running tests.
     *
     * @return bool|null
     */
    public function shouldEnableVarnish()
    {
        return $this->getValue('enable_varnish', 'ENABLE_VARNISH');
    }

    /**
     * Returns selenium server ip address.
     *
     * @return string|null
     */
    public function getSeleniumServerIp()
    {
        return $this->getValue('selenium_server_ip', 'SELENIUM_SERVER_IP');
    }

    /**
     * Returns which browser should be used when running selenium tests.
     *
     * @return string|null
     */
    public function getBrowserName()
    {
        return $this->getValue('browser_name', 'BROWSER_NAME');
    }

    /**
     * Returns path where to store screenshots on selenium test failure.
     *
     * @return string|null
     */
    public function getScreenShotsPath()
    {
        return $this->getValue('screen_shots_path', 'SCREENSHOTS_PATH');
    }

    /**
     * Returns url which should be used to display path to screenshots.
     *
     * @return string|null
     */
    public function getSeleniumScreenshotsUrl()
    {
        return $this->getValue('screen_shots_url', 'SCREENSHOTS_URL');
    }

    /**
     * Whether to run shop tests.
     *
     * @return string|null
     */
    public function shouldRunShopTests()
    {
        return $this->getValue('run_tests_for_shop');
    }

    /**
     * Whether to run module tests.
     *
     * @return string|null
     */
    public function shouldRunModuleTests()
    {
        return $this->getValue('run_tests_for_modules');
    }

    /**
     * Returns current test suite.
     *
     * @return null|string
     */
    public function getCurrentTestSuite()
    {
        if (is_null($this->currentTestSuite)) {
            $currentSuite = '';
            $testFilePath = end($_SERVER['argv']);
            if ($testFilePath == 'AllTestsUnit') {
                $currentSuite = getenv('TEST_SUITE');
            } else {
                $testSuites = $this->getTestSuites();
                $testFilePath = realpath($testFilePath);
                foreach ($testSuites as $suite) {
                    if (strpos($testFilePath, $suite) === 0) {
                        $currentSuite = $suite;
                        break;
                    }
                }
            }
            $this->currentTestSuite = $currentSuite ? $currentSuite : $this->getShopTestsPath();
        }

        return $this->currentTestSuite;
    }

    /**
     * Returns test suites.
     *
     * @return array
     */
    public function getTestSuites()
    {
        if (is_null($this->testSuites)) {
            $testSuites = $this->getModuleTestSuites();

            if ($this->shouldRunShopTests() && $this->getShopTestsPath()) {
                $testSuites[] = $this->getShopTestsPath();
            }
            $this->testSuites = $testSuites;
        }

        return $this->testSuites;
    }

    /**
     * Returns defined modules test suites.
     *
     * @return array
     */
    protected function getModuleTestSuites()
    {
        $testSuites = array();
        if ($this->shouldRunModuleTests()) {
            $modulesDir = $this->getShopPath() .'/modules/';
            foreach ($this->getModulesToTest() as $module) {
                if ($suitePath = realpath($modulesDir . $module .'/tests/')) {
                    $testSuites[] = $suitePath;
                }
            }
        }

        return $testSuites;
    }

    /**
     * Returns value for config parameter.
     *
     * @param string $param
     * @param string $alias
     *
     * @return string|array|null
     */
    private function getValue($param, $alias = null)
    {
        $value = array_key_exists($param, $this->configuration) ? $this->configuration[$param] : null;
        if ($alias && getenv($alias) !== false) {
            $value = getenv($alias);
        }

        return $value;
    }

    /**
     * Returns possible shop path.
     *
     * @param string $relativeShopPath
     * @return string
     */
    private function findShopPath($relativeShopPath)
    {
        $vendorBaseDir = $this->getVendorPath();
        $availablePaths = array(
            $vendorBaseDir .'../',
            $vendorBaseDir .'../../../',
            $vendorBaseDir .'../../../../',
        );

        $shopPath = '';
        foreach ($availablePaths as $path) {
            if (file_exists($path . '/config.inc.php')) {
                $shopPath = $path;
                break;
            }
        }

        return $shopPath ? $shopPath : $vendorBaseDir .'/../'. $relativeShopPath;
    }

    /**
     * Returns configuration file path.
     *
     * @return string
     */
    private function getConfigFileName()
    {
        return $this->getVendorPath() ."/../test_config.yml";
    }
}
