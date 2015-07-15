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

class ObjectValidator
{

    /**
     * @var array
     */
    private $_aErrors = array();

    /**
     * @param      $sClass
     * @param      $aExpectedParams
     * @param null $sOxId
     * @return bool
     */
    public function validate($sClass, $aExpectedParams, $sOxId = null)
    {
        $aObjectParams = $this->_getObjectParameters($sClass, array_keys($aExpectedParams), $sOxId);

        $blResult = true;
        foreach ($aExpectedParams as $sKey => $sExpectedValue) {
            $sObjectValue = $aObjectParams[$sKey];
            if ($sExpectedValue !== $sObjectValue) {
                $this->_setError("'$sExpectedValue' != '$sObjectValue' on key '$sKey'");
                $blResult = false;
            }
        }

        return $blResult;
    }

    /**
     * Returns formed error message if parameters was not valid
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $sMessage = '';
        $aErrors = $this->_getErrors();
        if (!empty($aErrors)) {
            $sMessage = "Expected and actual parameters do not match: \n";
            $sMessage .= implode("\n", $aErrors);
        }

        return $sMessage;
    }

    /**
     * Sets error message to error stack
     *
     * @param array $sMessage
     */
    protected function _setError($sMessage)
    {
        $this->_aErrors[] = $sMessage;
    }

    /**
     * Returns errors array
     *
     * @return array
     */
    protected function _getErrors()
    {
        return $this->_aErrors;
    }

    /**
     * Returns object parameters
     *
     * @param string $sClass
     * @param array  $aObjectParams
     * @param string $sOxId
     * @param string $sShopId
     *
     * @return array
     */
    protected function _getObjectParameters($sClass, $aObjectParams, $sOxId = null, $sShopId = null)
    {
        $oServiceCaller = new ServiceCaller();
        $oServiceCaller->setParameter('cl', $sClass);

        $sOxId = $sOxId ? $sOxId : 'lastInsertedId';
        $oServiceCaller->setParameter('oxid', $sOxId);
        $oServiceCaller->setParameter('classparams', $aObjectParams);

        return $oServiceCaller->callService('ShopObjectConstructor', $sShopId);
    }
}