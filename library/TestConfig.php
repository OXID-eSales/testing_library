<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\Facts\Edition\EditionSelector;
use Symfony\Component\Yaml\Yaml;

class TestConfig
{
    /** @var array */
    private $configuration;

    /** @var string */
    private $shopPath;

    /** @var string */
    private $charsetMode;

    /** @var string Shop edition. Either EE, PE or CE. */
    private $shopEdition;

    /** @var string Shop url. */
    private $shopUrl;

    /** @var string Currently running test suite path. */
    private $currentTestSuite;

    /** @var array All defined test suites. */
    private $testSuites;

    /** @var string Temporary directory. */
    private $tempDirectory;

    /** @var array Current module configuration. */
    private $moduleConfiguration;

    /**
     * Initiates configuration from configuration yaml file.
     */
    public function __construct()
    {
        $yamlFile = $this->getConfigFileName();
        if (!file_exists($yamlFile)) {
            die("Configuration file 'test_config.yml' was not found. Please refer to documentation for instructions.\n");
        }
        $yaml = Yaml::parse(file_get_contents($yamlFile));
        $this->configuration = array_merge($yaml['mandatory_parameters'], $yaml['optional_parameters']);
    }

    /**
     * Ensure that the edition specific unified namespace classes are properly generated.
     */
    static public function prepareUnifiedNamespaceClasses()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $unifiedNameSpaceClassMapProvider = new \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider($facts);
        $generator = new \OxidEsales\UnifiedNameSpaceGenerator\Generator($facts, $unifiedNameSpaceClassMapProvider);
        $generator->cleanupOutputDirectory();
        $generator->generate();
    }

    /**
     * Returns path to vendors directory.
     *
     * @return string
     */
    public function getVendorDirectory()
    {
        return TEST_LIBRARY_VENDOR_DIRECTORY;
    }

    /**
     * Returns path to shop source directory.
     *
     * @return string
     */
    public function getShopPath()
    {
        if (!$this->shopPath) {
            $this->shopPath = $this->getValue('shop_path');
            if (strpos($this->shopPath, '/') !== 0) {
                $this->shopPath = $this->findShopPath($this->shopPath);
            }
            $this->shopPath = realpath($this->shopPath) . '/';
        }

        return $this->shopPath;
    }

    /**
     * Returns shop edition
     *
     * @return array|null|string
     */
    public function getShopEdition()
    {
        if (is_null($this->shopEdition)) {
            require_once $this->getShopPath() . 'bootstrap.php';
            $editionSelector = new EditionSelector();
            $this->shopEdition = $editionSelector->getEdition();
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
        return $this->isSubShop() ? 2 : 1;
    }

    /**
     * Whether tested shop is subshop.
     *
     * @return bool
     */
    public function isSubShop()
    {
        return $this->getShopEdition() == 'EE' && $this->getValue('is_subshop');
    }

    /**
     * Returns shop url.
     *
     * @return string
     */
    public function getShopUrl()
    {
        if (is_null($this->shopUrl)) {
            $shopUrl = $this->getValue('shop_url');
            if (!$shopUrl) {
                $shopUrl = $shopUrl ? $shopUrl : $this->getConfigFile()->sShopURL;
            }
            $this->shopUrl = rtrim($shopUrl, '/') . '/';
        }

        return $this->shopUrl;
    }

    /**
     * Returns shop charset mode.
     *
     * @return string
     */
    public function getShopCharset()
    {
        if (is_null($this->charsetMode)) {
            $this->charsetMode = 'utf8';
        }

        return $this->charsetMode;
    }

    /**
     * Returns external server directory.
     * This directory should be in 'user@host:/directory/on/server' format.
     *
     * @return string
     */
    public function getRemoteDirectory()
    {
        return $this->getValue('remote_server_dir');
    }

    /**
     * Returns shop tests path.
     *
     * @return string|null
     */
    public function getShopTestsPath()
    {
        $partialPath = $this->getValue('shop_tests_path');

        return  $this->formFullPath($partialPath);
    }

    public function getEditionTestsPath(string $edition): string
    {
        $testsPath = $this->getShopTestsPath();

        if ($edition === EditionSelector::PROFESSIONAL) {
            $testsPath = $this->getVendorDirectory() . '/oxid-esales/tests-deprecated-pe/';
        } elseif ($edition === EditionSelector::ENTERPRISE) {
            $testsPath = $this->getVendorDirectory() . '/oxid-esales/tests-deprecated-ee/';
        }

        return $testsPath;
    }

    public function getModuleIds(): array
    {
        return $this->parseMultipleValues('module_ids');
    }

    public function getAdditionalTestPaths(): array
    {
        $testsPaths = array();
        $parsedConfigOptionValue = $this->parseMultipleValues('additional_test_paths');
        foreach ($parsedConfigOptionValue as $partialTestsPath) {
            $fullPath = $this->formFullPath($partialTestsPath);
            if ($fullPath) {
                $testsPaths[] = $fullPath;
            }
        }

        return $testsPaths;
    }

    public function getModulesToActivate(): array
    {
        $modulesToActivate = array();

        if ($this->shouldActivateAllModules()) {
            $modulesToActivate = $this->getModuleIds();
        } else {
            $current = $this->getCurrentTestSuite();

            foreach ($this->getModuleIds() as $moduleId) {
                if ($current === $this->getModuleTestSuitePath($moduleId)) {
                    $modulesToActivate[] = $moduleId;
                }
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
        return $this->getValue('shop_setup_path');
    }

    /**
     * Returns database restoration class name.
     *
     * @return string|null
     */
    public function getDatabaseRestorationClass()
    {
        return $this->getValue('database_restoration_class');
    }

    /**
     * Returns what serial to use when installing the shop.
     *
     * @return string|null
     */
    public function getShopSerial()
    {
        return $this->getValue('shop_serial');
    }

    /**
     * Whether to install shop before running tests.
     *
     * @return bool|null
     */
    public function shouldInstallShop()
    {
        return (bool)$this->getValue('install_shop');
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
     * Whether to dumb and restore the db when running the acceptance tests
     * By default restore database to keep backward compatibility.
     *
     * @return bool|null
     */
    public function shouldRestoreAfterAcceptanceTests()
    {
        $restoreDB = true;

        if (!is_null($this->getValue('restore_after_acceptance_tests'))) {
            $restoreDB = (bool)$this->getValue('restore_after_acceptance_tests');
        }

        return $restoreDB;
    }

    /**
     * Whether to dumb and restore the db after all tests finished in a test suite
     * By default restore database to keep backward compatibility.
     *
     * @return bool|null
     */
    public function shouldRestoreAfterUnitTests()
    {
        $restoreDB = true;

        if (!is_null($this->getValue('restore_after_unit_tests'))) {
            $restoreDB = (bool)$this->getValue('restore_after_unit_tests');
        }

        return $restoreDB;
    }

    /**
     * Whether to activate all modules when running tests.
     *
     * @return bool
     */
    public function shouldActivateAllModules()
    {
        return (bool) $this->getValue('activate_all_modules');
    }

    public function getTestDatabaseName()
    {
        return $this->getValue('test_database_name');
    }

    /**
     * Returns temp directory for storing tests data.
     *
     * @return bool|null
     */
    public function getTempDirectory()
    {
        if (is_null($this->tempDirectory)) {
            $this->tempDirectory = rtrim($this->getValue('tmp_path'), '/').'/';
        }

        return $this->tempDirectory;
    }

    /**
     * Whether to enable varnish when running tests.
     *
     * @return bool|null
     */
    public function shouldEnableVarnish()
    {
        return $this->getValue('enable_varnish');
    }

    /**
     * Returns selenium server ip address.
     *
     * @return string|null
     */
    public function getSeleniumServerIp()
    {
        return $this->getValue('selenium_server_ip');
    }

    /**
     * Returns selenium server port.
     *
     * @return string|null
     */
    public function getSeleniumServerPort()
    {
        return $this->getValue('selenium_server_port');
    }

    /**
     * Returns which browser should be used when running selenium tests.
     *
     * @return string|null
     */
    public function getBrowserName()
    {
        return $this->getValue('browser_name');
    }

    /**
     * Returns path where to store screenshots on selenium test failure.
     *
     * @return string|null
     */
    public function getScreenShotsPath()
    {
        return $this->getValue('screen_shots_path');
    }

    /**
     * Returns url which should be used to display path to screenshots.
     *
     * @return string|null
     */
    public function getScreenShotsUrl()
    {
        return $this->getValue('screen_shots_url');
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
     * Return how many times to try test before marking it as failure.
     * Could be used with unstable tests.
     *
     * @return int
     */
    public function getRetryTimes()
    {
        $retryTimes = 0;

        if (!is_null($this->getValue('retry_times_after_test_fail'))) {
            $retryTimes = (int)$this->getValue('retry_times_after_test_fail');
        }

        return $retryTimes;
    }

    /**
     * Returns current test suite.
     *
     * @return null|string
     */
    public function getCurrentTestSuite()
    {
        if (is_null($this->currentTestSuite)) {
            $currentSuite = getenv('TEST_SUITE');
            if (!$currentSuite) {
                $testSuites = $this->getTestSuites();
                $testFilePath = implode(",", $_SERVER['argv']);
                foreach ($testSuites as $suite) {
                    if (strpos($testFilePath, realpath($suite)) !== false) {
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
            $this->testSuites = $this->formTestSuites();
        }

        return $this->testSuites;
    }

    public function getModuleTestSuites(): array
    {
        $moduleTestSuites = [];
        if ($this->shouldRunModuleTests()) {
            foreach ($this->getModuleIds() as $moduleId) {
                $moduleTestSuites[] = $this->getModuleTestSuitePath($moduleId);
            }
        }

        return $moduleTestSuites;
    }

    private function getModuleTestSuitePath(string $moduleId): string
    {
        $testSuitePathForModule = '';

        if (isset($this->getModuleConfigurations()[$moduleId])) {
            //Delete at least 1 vendor
            $moduleDir = $this->getVendorDirectory() .
                substr($this->getModuleConfigurations()[$moduleId]->getModuleSource(), 7);

            if (is_dir($moduleDir . '/tests/')) {
                $testSuitePathForModule = $moduleDir . '/tests';
            } elseif (is_dir($moduleDir . '/Tests/')) {
                $testSuitePathForModule = $moduleDir . '/Tests';
            }
        }
        return $testSuitePathForModule;
    }

    private function getModuleConfigurations(): array
    {
        if (is_null($this->moduleConfiguration)) {
            $this->moduleConfiguration = ContainerFactory::getInstance()->getContainer()
            ->get(ShopConfigurationDaoBridgeInterface::class)
            ->get()
            ->getModuleConfigurations();
        }

        return $this->moduleConfiguration;
    }

    /**
     * Returns oxConfigFile from registry or creates new object
     *
     * @return \OxidEsales\Eshop\Core\ConfigFile
     */
    protected function getConfigFile()
    {
        if (class_exists('oxRegistry') || class_exists(\OxidEsales\Eshop\Core\Registry::Class)) {
            $configFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        } else {
            $shopPath = $this->getShopPath();
            $configFile = new \OxidEsales\Eshop\Core\ConfigFile($shopPath . "config.inc.php");
        }

        return $configFile;
    }

    /**
     * Returns configuration parameter value. First checks if environmental variable is set with the same uppercase name or provided one.
     *
     * @param string $param Parameter name.
     *
     * @return string|array|null
     */
    private function getValue($param)
    {
        $value = array_key_exists($param, $this->configuration) ? $this->configuration[$param] : null;

        $environmentalParam = strtoupper($param);
        if (getenv($environmentalParam) !== false) {
            $value = getenv($environmentalParam);
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
        $vendorBaseDir = $this->getVendorDirectory();
        $availablePaths = array(
            $vendorBaseDir .'../', // When vendor directory is in shop base directory
            $vendorBaseDir .'../../../', // When vendor directory is in /shop/dir/modules/testmodule/ directory
            $vendorBaseDir .'../../../../', // When vendor directory is in /shop/dir/modules/company/testmodule/ directory
        );

        $shopPath = '';
        foreach ($availablePaths as $path) {
            if (file_exists($path . '/config.inc.php')) {
                $shopPath = $path;
                break;
            }
        }

        return $shopPath ? $shopPath : $vendorBaseDir .'../'. $relativeShopPath;
    }

    /**
     * Returns configuration file path.
     *
     * @return string
     */
    private function getConfigFileName()
    {
        return $this->getVendorDirectory() ."../test_config.yml";
    }

    /**
     * @return array
     */
    private function formTestSuites()
    {
        $testSuites = $this->getModuleTestSuites();
        foreach ($this->getAdditionalTestPaths() as $testPaths) {
            $testSuites[] = $testPaths;
        }
        if ($this->shouldRunShopTests() && $this->getShopTestsPath()) {
            $testSuites[] = $this->getShopTestsPath();
        }

        return $testSuites;
    }

    private function parseMultipleValues(string $configOptionName): array
    {
        $multipleValues = [];
        if ($commaSeparatedValue = $this->getValue($configOptionName)) {
            $multipleValues = array_map('trim', explode(',', $commaSeparatedValue));
        }

        return $multipleValues;
    }

    /**
     * @param $partialPath
     * @return string
     */
    private function formFullPath($partialPath)
    {
        $testsPath = $partialPath;
        if (strpos($partialPath, '/') !== 0) {
            $testsPath = $this->getVendorDirectory() . '/../' . $partialPath;
        }

        return realpath($testsPath . '/');
    }
}
