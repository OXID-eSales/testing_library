<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;


require_once TEST_LIBRARY_HELPERS_PATH . 'oxArticleHelper.php';
require_once TEST_LIBRARY_HELPERS_PATH . 'oxSeoEncoderHelper.php';
require_once TEST_LIBRARY_HELPERS_PATH . 'oxDeliveryHelper.php';
require_once TEST_LIBRARY_HELPERS_PATH . 'oxManufacturerHelper.php';
require_once TEST_LIBRARY_HELPERS_PATH . 'oxVendorHelper.php';
require_once TEST_LIBRARY_HELPERS_PATH . 'oxAdminViewHelper.php';

/**
 * This class is used to backup and restore shop state during testing.
 */
class ShopStateBackup
{
    /** @var array Registry objects saved for restoration. */
    private $registryCache;

    /** @var array Request parameters saved for restoration. */
    private $requestCache;

    /**
     * Resets static variables of most classes.
     */
    public function resetStaticVariables()
    {
        \oxArticleHelper::cleanup();
        \oxSeoEncoderHelper::cleanup();
        \oxDeliveryHelper::cleanup();
        \oxManufacturerHelper::cleanup();
        \oxAdminViewHelper::cleanup();
        \oxVendorHelper::cleanup();
    }

    /**
     * Creates registry clone
     */
    public function backupRegistry()
    {
        $this->registryCache = array();
        foreach (\OxidEsales\Eshop\Core\Registry::getKeys() as $class) {
            $instance = \OxidEsales\Eshop\Core\Registry::get($class);
            $this->registryCache[$class] = clone $instance;
        }
    }

    /**
     * Cleans up the registry
     */
    public function resetRegistry()
    {
        $aRegKeys = \OxidEsales\Eshop\Core\Registry::getKeys();

        $aSkippedClasses = array();

        foreach ($aRegKeys as $sKey) {
            if (!in_array($sKey, $aSkippedClasses)) {
                $oInstance = null;
                if (!isset($this->registryCache[$sKey])) {
                    try {
                        $oNewInstance = oxNew($sKey);
                        $this->registryCache[$sKey] = $oNewInstance;
                    } catch (\OxidEsales\Eshop\Core\Exception\SystemComponentException $oException) {
                        \OxidEsales\Eshop\Core\Registry::set($sKey, null);
                        continue;
                    }
                }
                $oInstance = clone $this->registryCache[$sKey];
                \OxidEsales\Eshop\Core\Registry::set($sKey, $oInstance);
            }
        }
    }

    /**
     * Backs up global request variables for reverting them back after test run.
     */
    public function backupRequestVariables()
    {
        $this->requestCache['_SERVER'] = $_SERVER;
        $this->requestCache['_POST'] = $_POST;
        $this->requestCache['_GET'] = $_GET;
        $this->requestCache['_SESSION'] = $_SESSION;
        $this->requestCache['_COOKIE'] = $_COOKIE;
    }

    /**
     * Sets global request variables to backed up ones after every test run.
     */
    public function resetRequestVariables()
    {
        $_SERVER = $this->requestCache['_SERVER'];
        $_POST = $this->requestCache['_POST'];
        $_GET = $this->requestCache['_GET'];
        $_SESSION = $this->requestCache['_SESSION'];
        $_COOKIE = $this->requestCache['_COOKIE'];
    }
}
