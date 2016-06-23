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

namespace OxidEsales\TestingLibrary;

use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\ServiceFactory;

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
        define('TMP_PATH', $this->getTestConfig()->getTempDirectory());

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
