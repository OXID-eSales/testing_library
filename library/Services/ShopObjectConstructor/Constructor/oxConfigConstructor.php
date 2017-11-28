<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;



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
     * @return \OxidEsales\Eshop\Core\Config
     */
    protected function _createObject($className)
    {
        return oxNew(\OxidEsales\Eshop\Core\Config::class);
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
        $type = null;
        if (isset($configParameters['type'])) {
            $type = $configParameters['type'];
        }

        $value = null;
        if (isset($configParameters['value'])) {
            $value = $configParameters['value'];
        }

        $module = null;
        if (isset($configParameters['module'])) {
            $module = $configParameters['module'];
        }

        if (($type == "arr" || $type == 'aarr') && !is_array($value)) {
            $value = unserialize(htmlspecialchars_decode($value));
        }
        return !empty($type) ? array($type, $configKey, $value, null, $module) : false;
    }
}
