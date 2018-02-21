<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Bootstrap;

use OxidEsales\TestingLibrary\AcceptanceTestCase;
use OxidEsales\TestingLibrary\FileCopier;

class SeleniumBootstrap extends BootstrapBase
{
    /** @var int Whether to add demo data when installing the shop. */
    protected $addDemoData = 1;

    /**
     * Initiates shop before testing.
     */
    public function init()
    {
        parent::init();

        define("SHOP_EDITION", ($this->getTestConfig()->getShopEdition() == 'EE') ? 'EE' : 'PE_CE');

        $this->prepareScreenShots();
        $this->copyTestFilesToShop();

        /** @var \OxidEsales\Eshop\Core\Config $config */
        $config = oxNew(\OxidEsales\Eshop\Core\Config::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $config);

        /** Reset static variable in \OxidEsales\Eshop\Core\Base class, which is base class for every class. */
        $config->setConfig($config);

        register_shutdown_function(function () {
            AcceptanceTestCase::stopMinkSession();
        });
    }

    /**
     * Creates screenshots directory if it does not exists.
     */
    public function prepareScreenShots()
    {
        $screenShotsPath = $this->getTestConfig()->getScreenShotsPath();
        if ($screenShotsPath && !is_dir($screenShotsPath)) {
            mkdir($screenShotsPath, 0777, true);
        }
    }

    /**
     * Sets global constants, as these are still used a lot in tests.
     * This is used to maintain backwards compatibility, but should not be used anymore in new code.
     */
    protected function setGlobalConstants()
    {
        parent::setGlobalConstants();
        $testConfig = $this->getTestConfig();

        /** @deprecated use TestConfig::getShopUrl() */
        define('shopURL', $testConfig->getShopUrl());

        /** @deprecated use TestConfig::getShopId() */
        define('oxSHOPID', $testConfig->getShopId());

        /** @deprecated use TestConfig::isSubShop() */
        define('isSUBSHOP', $testConfig->isSubShop());
    }

    /**
     * Some test files are needed to successfully run selenium tests.
     * Currently only files needed for clearing cookies are copied.
     */
    public function copyTestFilesToShop()
    {
        $config = $this->getTestConfig();
        $target = $config->getRemoteDirectory() ? $config->getRemoteDirectory().'/_cc.php' : $config->getShopPath().'/_cc.php';
        $fileCopier = new FileCopier();
        $fileCopier->copyFiles(TEST_LIBRARY_PATH .'_cc.php', $target, true);
    }
}
