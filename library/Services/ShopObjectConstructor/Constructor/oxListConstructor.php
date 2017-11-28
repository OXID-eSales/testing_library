<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;

use Iterator;

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
     * @param array $parameters
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
     * @param \OxidEsales\Eshop\Core\Model\ListModel|Iterator $oList
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
     * @param \OxidEsales\Eshop\Core\Model\BaseModel|object $object
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
