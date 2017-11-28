<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\ServiceFactory;
use OxidEsales\TestingLibrary\Services\Files\ChangeExceptionLogRights;

/**
 * Class for calling services. Services must already exist in shop.
 */
class ServiceCaller
{
    /** @var array Service parameters. Will be passed to service. */
    private $parameters = array();

    /** @var TestConfig */
    private $config;

    /** @var bool Whether services was copied to the shop. */
    private static $servicesCopied = false;

    /**
     * Initiates class dependencies.
     *
     * @param TestConfig $config
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = new TestConfig();
        }
        $this->config = $config;
    }

    /**
     * Sets given parameters.
     *
     * @param string $sKey Parameter name.
     * @param string $aVal Parameter value.
     */
    public function setParameter($sKey, $aVal)
    {
        $this->parameters[$sKey] = $aVal;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Call shop service to execute code in shop.
     *
     * @param string $serviceName
     * @param string $shopId
     *
     * @example call to update information to database.
     *
     * @throws \Exception
     *
     * @return string $sResult
     */
    public function callService($serviceName, $shopId = null)
    {
        $testConfig = $this->getTestConfig();
        if (!is_null($shopId) && $testConfig->getShopEdition() == 'EE') {
            $this->setParameter('shp', $shopId);
        } elseif ($testConfig->isSubShop()) {
            $this->setParameter('shp', $testConfig->getShopId());
        }

        if ($testConfig->getRemoteDirectory()) {
            $response = $this->callRemoteService($serviceName);
        } else {
            $this->callLocalService(ChangeExceptionLogRights::class);

            $response = $this->callLocalService($serviceName);
        }

        $this->parameters = array();

        return $response;
    }

    /**
     * Calls service on remote server.
     *
     * @param string $serviceName
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function callRemoteService($serviceName)
    {
        if (!self::$servicesCopied) {
            self::$servicesCopied = true;
            $this->copyServicesToShop();
        }

        $oCurl = new Curl();

        $this->setParameter('service', $serviceName);

        $oCurl->setUrl($this->getTestConfig()->getShopUrl() . '/Services/service.php');
        $oCurl->setParameters($this->getParameters());

        $sResponse = $oCurl->execute();

        if ($oCurl->getStatusCode() >= 300) {
            $sResponse = $oCurl->execute();
        }

        return $this->unserializeResponse($sResponse);
    }

    /**
     * Calls service on local server.
     *
     * @param string $serviceName
     *
     * @return mixed|null
     */
    protected function callLocalService($serviceName)
    {
        if (!defined('TMP_PATH')) {
            define('TMP_PATH', $this->getTestConfig()->getTempDirectory());
        }

        $config = new ServiceConfig($this->getTestConfig()->getShopPath(), $this->getTestConfig()->getTempDirectory());
        $config->setShopEdition($this->getTestConfig()->getShopEdition());

        $serviceCaller = new ServiceFactory($config);
        $request = new Request($this->getParameters());
        $service = $serviceCaller->createService($serviceName);

        return $service->init($request);
    }

    /**
     * Returns tests config object.
     *
     * @return TestConfig
     */
    protected function getTestConfig()
    {
        return $this->config;
    }

    /**
     * Copies services directory to shop.
     */
    protected function copyServicesToShop()
    {
        $fileCopier = new FileCopier();
        $target = $this->getTestConfig()->getRemoteDirectory() . '/Services';
        $fileCopier->copyFiles(TEST_LIBRARY_PATH.'/Services', $target, true);
    }

    /**
     * Unserializes given string. Throws exception if incorrect string is passed
     *
     * @param string $response
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function unserializeResponse($response)
    {
        $result = unserialize($response);
        if ($response !== 'b:0;' && $result === false) {
            throw new \Exception(substr($response, 0, 5000));
        }

        return $result;
    }
}
