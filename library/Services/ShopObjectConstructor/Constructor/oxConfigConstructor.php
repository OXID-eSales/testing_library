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
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;

use oxConfig;

/**
 * Class oxConfigCaller
 */
class oxConfigConstructor extends ObjectConstructor
{

    /**
     * Skip loading of config object, as it is already loaded
     *
     * @param string $objectId
     */
    public function load($objectId)
    {
    }

    /**
     * Sets class parameters
     *
     * @param array $classParams
     *
     * @return array
     */
    public function setClassParameters($classParams)
    {
        $values = array();
        foreach ($classParams as $sConfKey => $configParameters) {
            if (is_int($sConfKey)) {
                $values[$configParameters] = $this->getObject()->getConfigParam($configParameters);
            } else {
                $aFormedParams = $this->_formSaveConfigParameters($sConfKey, $configParameters);
                if ($aFormedParams) {
                    $this->callFunction("saveShopConfVar", $aFormedParams);
                }
            }
        }

        return $values;
    }

    /**
     * Returns created object to work with
     *
     * @param string $className
     *
     * @return oxConfig
     */
    protected function _createObject($className)
    {
        return oxNew('oxConfig');
    }

    /**
     * Forms parameters for saveShopConfVar function from given parameters
     *
     * @param string $configKey
     * @param array  $configParameters
     * @return array|bool
     */
    private function _formSaveConfigParameters($configKey, $configParameters)
    {
        $type = $configParameters['type'] ? $configParameters['type'] : null;
        $value = $configParameters['value'] ? $configParameters['value'] : null;
        $module = $configParameters['module'] ? $configParameters['module'] : null;

        if (($type == "arr" || $type == 'aarr') && !is_array($value)) {
            $value = unserialize(htmlspecialchars_decode($value));
        }
        return !empty($type) ? array($type, $configKey, $value, null, $module) : false;
    }
}
