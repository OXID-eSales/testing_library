<?php

use Symfony\Component\Yaml\Yaml;
if (!defined('TESTS_DIRECTORY')) {
    define('TESTS_DIRECTORY', __DIR__ . '/../');
}

class Test_Config
{
    /** @var array */
    private $configuration;

    /** @var string */
    private $shopPath;

    /**
     * Initiates configuration from configuration yaml file.
     */
    public function __construct()
    {
        $yaml = Yaml::parse(file_get_contents($this->getConfigFileName()));
        $this->configuration = array_merge($yaml['mandatory_parameters'], $yaml['optional_parameters']);
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

        return realpath($this->shopPath) .'/';
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
     * Returns shop id
     *
     * @return int|string
     */
    public function getShopId()
    {
        $sShopId = "oxbaseshop";
        if (OXID_VERSION_EE) :
            $sShopId = getenv('oxSHOPID') ? (int)getenv('oxSHOPID') : ($this->getValue('shop_id') ? 2 : 1);
        endif;

        return $sShopId;
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
        $sShopUrl = $this->getValue('shop_url', 'SHOP_URL');
        if (!$sShopUrl) {
            include_once oxPATH . 'core/oxconfigfile.php';
            $oConfigFile = new oxConfigFile(oxPATH . "config.inc.php");
            $sShopUrl = $sShopUrl ? $sShopUrl : $oConfigFile->sShopURL;
        }

        return rtrim($sShopUrl, '/') . '/';
    }

    /**
     * Returns shop tests path.
     *
     * @return string|null
     */
    public function getShopTestsPath()
    {
        $sTestsPath = $this->getValue('shop_tests_path');
        if (strpos($sTestsPath, '/') !== 0) {
            $sTestsPath = $this->getShopPath() . $sTestsPath;
        }

        return rtrim($sTestsPath, '/') .'/';
    }

    /**
     * Returns array of paths to all modules which should be tested.
     *
     * @return array|null
     */
    public function getModulePaths()
    {
        return $this->getValue('modules_path', 'MODULES_PATH');
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
        return (bool) $this->getValue('install_shop', 'INSTALL_SHOP');
    }

    /**
     * Whether to restore shop database after running all the tests.
     *
     * @return bool|null
     */
    public function shouldRestoreShopAfterTestsSuite()
    {
        return (bool) $this->getValue('restore_shop_after_tests_suite');
    }

    /**
     * Whether create separate directory in shop to store database dump.
     *
     * @return bool|null
     */
    public function shouldUseSeparateDbDumpDirectory()
    {
        return (bool) $this->getValue('special_db_dump_folder');
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
    public function getSeleniumScreenshotsPath()
    {
        return $this->getValue('selenium_screen_shots_path', 'SELENIUM_SCREENSHOTS_PATH');
    }

    /**
     * Returns url which should be used to display path to screenshots.
     *
     * @return string|null
     */
    public function getSeleniumScreenshotsUrl()
    {
        return $this->getValue('selenium_screen_shots_url', 'SELENIUM_SCREENSHOTS_URL');
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
     * @param string $relativeShopPath
     * @return string
     */
    private function findShopPath($relativeShopPath)
    {
        $vendorBaseDir = $this->getVendorBasePath();
        $availablePaths = array(
            $vendorBaseDir,
            $vendorBaseDir .'../../',
            $vendorBaseDir .'../../../',
        );

        $shopPath = '';
        foreach ($availablePaths as $path) {
            if (file_exists($path .'/config.inc.php')) {
                $shopPath = $path;
                break;
            }
        }

        return $shopPath ? $shopPath : $vendorBaseDir . $relativeShopPath;
    }

    /**
     * @return string
     */
    private function getVendorBasePath()
    {
        $vendorBasePath = TESTS_DIRECTORY ."../../../";
        if (!file_exists($vendorBasePath.'vendor')) {
            $vendorBasePath = TESTS_DIRECTORY;
        }
        return $vendorBasePath;
    }

    /**
     * Returns configuration file path.
     *
     * @return string
     */
    private function getConfigFileName()
    {
        return $this->getVendorBasePath() ."test_config.yml";
    }
}
