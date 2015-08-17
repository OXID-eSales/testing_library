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

use Iterator;
use oxBase;
use oxList;

/**
 * Class oxConfigCaller
 */
class oxListConstructor extends ObjectConstructor
{
    /**
     * Skip loading of config object, as it is already loaded
     *
     * @param string $objectId
     */
    public function load($objectId)
    {
        $this->getObject()->init($objectId, $objectId);
    }

    /**
     * Calls object function with given parameters
     *
     * @param string $functionName
     * @param string $parameters
     * @return mixed
     */
    public function callFunction($functionName, $parameters)
    {
        if ($functionName == 'getList') {
            $oObject = $this->getObject();
            $mResponse = $this->_formArrayFromList($oObject->getList());
        } else {
            $mResponse = parent::callFunction($functionName, $parameters);
        }

        return $mResponse;
    }

    /**
     * Returns formed array with data from given list
     *
     * @param oxList|Iterator $oList
     * @return array
     */
    protected function _formArrayFromList($oList)
    {
        $aData = array();
        foreach ($oList as $sKey => $object) {
            $aData[$sKey] = $this->_getObjectFieldValues($object);
        }

        return $aData;
    }

    /**
     * Returns object field values
     *
     * @param oxBase|object $object
     *
     * @return array
     */
    protected function _getObjectFieldValues($object)
    {
        $result = array();
        $fields = $object->getFieldNames();
        $tableName = $object->getCoreTableName();
        foreach ($fields as $field) {
            $fieldName = $tableName.'__'.$field;
            $result[$field] = $object->$fieldName->value;
        }

        return $result;
    }
}
