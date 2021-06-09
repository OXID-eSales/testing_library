<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\ShopInstaller;

use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Edition\EditionPathProvider;
use OxidEsales\Eshop\Core\Edition\EditionRootPathProvider;
use OxidEsales\Eshop\Core\Edition\EditionSelector;
use OxidEsales\EshopCommunity\Setup\Core;
use OxidEsales\TestingLibrary\Services\Library\Cache;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use OxidEsales\TestingLibrary\Services\Library\CliExecutor;
use OxidEsales\EshopProfessional\Core\Serial;
use OxidEsales\TestingLibrary\TestConfig;
use OxidEsales\EshopCommunity\Setup\Utilities;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * Class for shop installation.
 */
class ShopInstaller implements ShopServiceInterface
{
    /** @var DatabaseHandler */
    private $dbHandler;

    /** @var ServiceConfig */
    private $serviceConfig;

    /** @var ConfigFile */
    private $shopConfig;

    /** @var EditionPathProvider */
    private $editionPathProvider;

    /**
     * Includes configuration files.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->serviceConfig = $config;

        $this->shopConfig = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $this->dbHandler = new DatabaseHandler($this->shopConfig);
    }

    /**
     * Starts installation of the shop.
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function init($request)
    {
        if (!class_exists('\OxidEsales\EshopCommunity\Setup\Setup')) {
            throw new \Exception("Shop Setup directory has to be present!");
        }

        $cache = new Cache();
        $cache->clearTemporaryDirectory();

        $serialNumber = $request->getParameter('serial', false);
        $serialNumber = $serialNumber ? $serialNumber : $this->getDefaultSerial();

        $this->setupDatabase();

        if ($tempDir = $request->getParameter('tempDirectory')) {
            $this->insertConfigValue('string', 'sCompileDir', $tempDir);
        }
        $this->insertConfigValue('int', 'sOnlineLicenseNextCheckTime', time() + 25920000);

        if ($request->getParameter('addDemoData', false)) {
            $this->insertDemoData();
        }

        $this->setConfigurationParameters();

        $this->setSerialNumber($serialNumber);

        /** @var \OxidEsales\Eshop\Core\Theme $oTheme */
        $oTheme = oxNew(\OxidEsales\Eshop\Core\Theme::class);
        $oTheme->load("flow");
        $oTheme->activate();

        $config = $this->getShopConfig();
        $default = property_exists($config, 'turnOnVarnish') ? $config->turnOnVarnish : false;
        if ($request->getParameter('turnOnVarnish', $default)) {
            $this->turnVarnishOn();
        }
    }

    /**
     * Sets up database.
     */
    public function setupDatabase()
    {
        $dbHandler = $this->getDbHandler();

        $dbHandler->getDbConnection()->exec('DROP DATABASE IF EXISTS`' . $dbHandler->getDbName() . '`');
        $dbHandler->getDbConnection()->exec('create database `' . $dbHandler->getDbName() . '` collate ' . $dbHandler->getCharsetMode() . '_general_ci');

        $baseEditionPathProvider = new EditionPathProvider(new EditionRootPathProvider(new EditionSelector(EditionSelector::COMMUNITY)));

        $dbHandler->import($baseEditionPathProvider->getDatabaseSqlDirectory() . "/database_schema.sql");
        $dbHandler->import($baseEditionPathProvider->getDatabaseSqlDirectory() . "/initial_data.sql");

        $output = new ConsoleOutput();
        $output->setVerbosity(ConsoleOutputInterface::VERBOSITY_QUIET);

        $utilities = new Utilities();
        $utilities->executeExternalDatabaseMigrationCommand($output);

        $this->callShellDbViewsRegenerate();
    }

    protected function callShellDbViewsRegenerate()
    {
        $testConfig = new TestConfig();
        $vendorDir = $testConfig->getVendorDirectory();

        $php = getenv('PHPBIN') ? getenv('PHPBIN') . ' ': '';

        CliExecutor::executeCommand( $php . '"' . $vendorDir . '/bin/oe-eshop-db_views_generate"');
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function detectEncodingOfFile($filename)
    {
        $encoding = '';
        $content = file_get_contents($filename);
        if ($content !== false) {
            $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true);
        }

        return $encoding;
    }

    /**
     * Inserts test demo data to shop.
     */
    public function insertDemoData()
    {
        $testConfig = new TestConfig();
        $testDirectory = $testConfig->getEditionTestsPath($testConfig->getShopEdition());
        $this->getDbHandler()->import($testDirectory . "/Fixtures/testdemodata.sql");
    }

    /**
     * Inserts missing configuration parameters
     */
    public function setConfigurationParameters()
    {
        $dbHandler = $this->getDbHandler();
        $sShopId = $this->getShopId();

        $dbHandler->query("delete from oxconfig where oxvarname in ('iSetUtfMode','blSendTechnicalInformationToOxid');");
        $dbHandler->query(
            "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue) values " .
            "('config1', '{$sShopId}', 'iSetUtfMode',       'str',  '0' )," .
            "('config2', '{$sShopId}', 'blSendTechnicalInformationToOxid', 'bool', '1')"
        );
    }

    /**
     * Adds serial number to shop.
     *
     * @param string $serialNumber
     */
    public function setSerialNumber($serialNumber = null)
    {
        if (strtolower($this->getShopConfig()->getVar('edition')) !== strtolower(EditionSelector::COMMUNITY)
            && class_exists(Serial::class))
        {
            $dbHandler = $this->getDbHandler();

            $shopId = $this->getShopId();

            $serial = new Serial();
            $serial->setEd($this->getServiceConfig()->getShopEdition() == 'EE' ? 2 : 1);

            $serial->isValidSerial($serialNumber);

            $maxDays = $serial->getMaxDays($serialNumber);
            $maxArticles = $serial->getMaxArticles($serialNumber);
            $maxShops = $serial->getMaxShops($serialNumber);

            $dbHandler->query("update oxshops set oxserial = '{$serialNumber}'");
            $dbHandler->query("delete from oxconfig where oxvarname in ('aSerials','sTagList','IMD','IMA','IMS')");
            $dbHandler->query(
                "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue) values " .
                "('serial1', '{$shopId}', 'aSerials', 'arr', '" . serialize(array($serialNumber)) . "')," .
                "('serial2', '{$shopId}', 'sTagList', 'str', '" . time() . "')," .
                "('serial3', '{$shopId}', 'IMD',      'str', '" . $maxDays . "')," .
                "('serial4', '{$shopId}', 'IMA',      'str', '" . $maxArticles . "')," .
                "('serial5', '{$shopId}', 'IMS',      'str', '" . $maxShops . "')"
            );
        }
    }

    /**
     * Converts shop to utf8.
     */
    public function convertToUtf()
    {
        $dbHandler = $this->getDbHandler();

        $rs = $dbHandler->query(
            "SELECT oxvarname, oxvartype, oxvarvalue
                       FROM oxconfig
                       WHERE oxvartype IN ('str', 'arr', 'aarr')"
        );

        while ( (false !== $rs) && ($aRow = $rs->fetch())) {
            if ($aRow['oxvartype'] == 'arr' || $aRow['oxvartype'] == 'aarr') {
                $aRow['oxvarvalue'] = unserialize($aRow['oxvarvalue']);
            }
            if (!empty($aRow['oxvarvalue']) && !is_int($aRow['oxvarvalue'])) {
                $this->updateConfigValue($aRow['oxid'], $this->stringToUtf($aRow['oxvarvalue']));
            }
        }

        // Change currencies value to same as after 4.6 setup because previous encoding break it.
        $shopId = 1;

        $query = "REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
            ('3c4f033dfb8fd4fe692715dda19ecd28', $shopId, '', 'aCurrencies', 'arr', 'a:4:{i:0;s:23:\"EUR@ 1.00@ ,@ .@ €@ 2\";i:1;s:24:\"GBP@ 0.8565@ .@  @ £@ 2\";i:2;s:40:\"CHF@ 1.4326@ ,@ .@ <small>CHF</small>@ 2\";i:3;s:23:\"USD@ 1.2994@ .@  @ $@ 2\";}');";

        $dbHandler->query($query);
    }

    /**
     * Turns varnish on.
     */
    public function turnVarnishOn()
    {
        $dbHandler = $this->getDbHandler();

        $dbHandler->query("DELETE from oxconfig WHERE oxshopid = 1 AND oxvarname in ('iLayoutCacheLifeTime', 'blReverseProxyActive');");
        $dbHandler->query(
            "INSERT INTO oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue) VALUES
              ('35863f223f91930177693956aafe69e6', 1, 'iLayoutCacheLifeTime', 'str', '3600'),
              ('dbcfca66eed01fd43963443d35b109e0', 1, 'blReverseProxyActive',  'bool', '1');"
        );
    }

    /**
     * @return ConfigFile
     */
    protected function getShopConfig()
    {
        return $this->shopConfig;
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->serviceConfig;
    }

    /**
     * @return DatabaseHandler
     */
    protected function getDbHandler()
    {
        return $this->dbHandler;
    }

    /**
     * Returns default demo serial number for testing.
     *
     * @return string
     */
    protected function getDefaultSerial()
    {
        if ($this->getServiceConfig()->getShopEdition() != 'CE') {
            $core = new Core();
            /** @var \OxidEsales\EshopProfessional\Setup\Setup|\OxidEsales\EshopEnterprise\Setup\Setup $setup */
            $setup = $core->getInstance('Setup');
            return $setup->getDefaultSerial();
        }

        return null;
    }

    /**
     * @return EditionPathProvider
     */
    protected function getEditionPathProvider()
    {
        if (is_null($this->editionPathProvider)) {
            $editionPathSelector = new EditionRootPathProvider(new EditionSelector());
            $this->editionPathProvider = new EditionPathProvider($editionPathSelector);
        }

        return $this->editionPathProvider;
    }

    /**
     * Returns shop id.
     *
     * @return string
     */
    private function getShopId()
    {
        return '1';
    }

    /**
     * Insert new configuration value to database.
     *
     * @param string $type
     * @param string $name
     * @param string $value
     */
    private function insertConfigValue($type, $name, $value)
    {
        $dbHandler = $this->getDbHandler();
        $shopId = 1;
        $oxid = md5("${name}_1");

        $dbHandler->query("DELETE from oxconfig WHERE oxvarname = '$name';");
        $dbHandler->query("REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
            ('$oxid', $shopId, '', '$name', '$type', '{$value}');");
        if ($this->getServiceConfig()->getShopEdition() == EditionSelector::ENTERPRISE) {
            $oxid = md5("${name}_subshop");
            $shopId = 2;
            $dbHandler->query("REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
                ('$oxid', $shopId, '', '$name', '$type', '{$value}');");
        }
    }

    /**
     * Updates configuration value.
     *
     * @param string $id
     * @param string $value
     */
    private function updateConfigValue($id, $value)
    {
        $dbHandler = $this->getDbHandler();

        $value = is_array($value) ? serialize($value) : $value;
        $value = $dbHandler->escape($value);
        $dbHandler->query("update oxconfig set oxvarvalue = '{$value}' where oxvarname = '{$id}';");
    }

    /**
     * Converts input string to utf8.
     *
     * @param string $input String for conversion.
     *
     * @return array|string
     */
    private function stringToUtf($input)
    {
        if (is_array($input)) {
            $temp = array();
            foreach ($input as $key => $value) {
                $temp[$this->stringToUtf($key)] = $this->stringToUtf($value);
            }
            $input = $temp;
        } elseif (is_string($input)) {
            $input = iconv('iso-8859-15', 'utf-8', $input);
        }

        return $input;
    }
}
