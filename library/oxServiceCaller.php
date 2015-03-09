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

require_once 'oxTestCurl.php';

/**
 * Class for calling services. Services must already exist in shop.
 */
class oxServiceCaller
{
    /** @var array */
    private $_aParameters = array();

    /** @var oxTestConfig */
    private $config;

    /** @var bool Where services copied to the shop. */
    private static $servicesCopied = false;

    /**
     * If remote shop directory is provided, copies services to it.
     *
     * @param oxTestConfig $config
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = new oxTestConfig();
        }
        $this->config = $config;

        if ($config->getRemoteDirectory() && !self::$servicesCopied) {
            self::$servicesCopied = true;
            $this->copyServicesToShop();
        }
    }

    /**
     * Sets given parameters.
     *
     * @param string $sKey Parameter name.
     * @param string $aVal Parameter value.
     */
    public function setParameter($sKey, $aVal)
    {
        $this->_aParameters[$sKey] = $aVal;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_aParameters;
    }

    /**
     * Call shop service to execute code in shop.
     *
     * @param string $serviceName
     * @param string $shopId
     *
     * @example call to update information to database.
     *
     * @throws Exception
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
            $sResponse = $this->callRemoteService($serviceName);
        } else {
            $sResponse = $this->callLocalService($serviceName);
        }

        $this->_aParameters = array();
        return $sResponse;
    }

    /**
     * Calls service on remote server.
     *
     * @param $sServiceName
     * @return string
     * @throws Exception
     */
    protected function callRemoteService($sServiceName)
    {
        $testConfig = $this->getTestConfig();
        $oCurl = new oxTestCurl();

        $this->setParameter('service', $sServiceName);

        $oCurl->setUrl($testConfig->getShopUrl() . '/Services/service.php');
        $oCurl->setParameters($this->getParameters());

        $sResponse = $oCurl->execute();

        if ($oCurl->getStatusCode() >= 300) {
            $sResponse = $oCurl->execute();
        }

        return $this->unserializeString($sResponse);
    }

    /**
     * Calls service on local server.
     *
     * @param $serviceName
     * @return string
     */
    protected function callLocalService($serviceName)
    {
        require_once TEST_LIBRARY_PATH .'/Services/ServiceCaller.php';
        require_once TEST_LIBRARY_PATH . '/Services/Request.php';

        $serviceCaller = new ServiceCaller();

        $request = new Request($this->getParameters());
        return $serviceCaller->callService($serviceName, $request);
    }
    
    /**
     * Returns tests config object.
     *
     * @return oxTestConfig
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
        $oFileCopier = new oxFileCopier();
        $sTarget = $this->getTestConfig()->getRemoteDirectory().'/Services';
        $oFileCopier->copyFiles(TEST_LIBRARY_PATH.'/Services', $sTarget, true);
    }

    /**
     * Unserializes given string. Throws exception if incorrect string is passed
     *
     * @param string $sString
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function unserializeString($sString)
    {
        $mResult = unserialize($sString);
        if ($sString !== 'b:0;' && $mResult === false) {
            throw new Exception(substr($sString, 0, 500));
        }

        return $mResult;
    }
}
