<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link http://www.oxid-esales.com
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 */

require_once 'oxTestCurl.php';

/**
 * Class for calling services. Services must already exist in shop.
 */
class oxServiceCaller
{

    /** @var array */
    private $_aParameters = array();

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
     * @param string $sServiceName
     * @param string $sShopId
     *
     * @example call to update information to database.
     *
     * @throws Exception
     *
     * @return string $sResult
     */
    public function callService($sServiceName, $sShopId = null)
    {
        if ($sShopId && oxSHOPID != 'oxbaseshop') {
            $this->setParameter('shp', $sShopId);
        } elseif (isSUBSHOP) {
            $this->setParameter('shp', oxSHOPID);
        }

        $oCurl = new oxTestCurl();

        $sShopUrl = shopURL . '/Services/service.php';
        $this->setParameter('service', $sServiceName);

        $oCurl->setUrl($sShopUrl);
        $oCurl->setParameters($this->getParameters());

        $sResponse = $oCurl->execute();

        if ($oCurl->getStatusCode() >= 300) {
            $sResponse = $oCurl->execute();
        }

        $this->_aParameters = array();

        return $this->_unserializeString($sResponse);
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
    private function _unserializeString($sString)
    {
        $mResult = unserialize($sString);
        if ($sString !== 'b:0;' && $mResult === false) {
            throw new Exception(substr($sString, 0, 500));
        }

        return $mResult;
    }
}
