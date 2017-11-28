<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;

use Exception;

/**
 * Class ObjectCaller
 */
class ObjectConstructor
{
    /**
     * @var object
     */
    protected $object = null;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->object = $this->_createObject($className);
    }

    /**
     * Returns constructed object
     *
     * @return \OxidEsales\Eshop\Core\Model\BaseModel|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Loads object by given id
     *
     * @param mixed $objectId
     *
     * @throws Exception
     */
    public function load($objectId)
    {
        if (!empty($objectId)) {
            $blResult = is_array($objectId)? $this->_loadByArray($objectId) : $this->_loadById($objectId);
            if ($blResult === false) {
                $sClass = get_class($this->getObject());
                throw new Exception("Failed to load $sClass with id $objectId");
            }
        }
    }

    /**
     * @param string $objectId
     *
     * @return bool|mixed
     */
    protected function _loadById($objectId)
    {
        if ($objectId == 'lastInsertedId') {
            $objectId = $this->_getLastInsertedId();
        }
        $object = $this->getObject();
        $result = $object->load($objectId);

        if ($result && $object->getId() != $objectId) {
            $result = $object->load($objectId);
        }

        return $result;
    }

    /**
     * @param array $objectIds
     *
     * @return mixed
     */
    protected function _loadByArray($objectIds)
    {
        $function = key($objectIds);
        $id = current($objectIds);

        return $this->getObject()->$function($id);
    }

    /**
     * Sets class parameters
     *
     * @param array $classParams
     * @return array
     */
    public function setClassParameters($classParams)
    {
        $object = $this->getObject();
        $tableName = $object->getCoreTableName();
        $values = array();
        foreach ($classParams as $sParamKey => $paramValue) {
            if (is_int($sParamKey)) {
                $fieldName = $this->_getFieldName($tableName, $paramValue);
                $values[$paramValue] = $object->$fieldName->value;
            } else {
                $fieldName = $this->_getFieldName($tableName, $sParamKey);
                if (is_string($paramValue)) {
                    $paramValue = html_entity_decode($paramValue);
                }
                $object->$fieldName = new \OxidEsales\Eshop\Core\Field($paramValue);
            }
        }

        return $values;
    }

    /**
     * Calls object function with given parameters.
     *
     * @param string $functionName
     * @param array  $parameters
     *
     * @return mixed
     */
    public function callFunction($functionName, $parameters)
    {
        $parameters = is_array($parameters) ? $parameters : array();
        $response = call_user_func_array(array($this->getObject(), $functionName), $parameters);

        return $response;
    }

    /**
     * Returns created object to work with
     *
     * @param string $className
     *
     * @return object
     */
    protected function _createObject($className)
    {
        return oxNew($className);
    }

    /**
     * @param string $tableName
     * @param string $paramValue
     *
     * @return string
     */
    protected function _getFieldName($tableName, $paramValue)
    {
        $sResult = $tableName . '__' . $paramValue;
        if (strpos($paramValue, '__') !== false) {
            $sResult = $paramValue;
        }
        return strtolower($sResult);
    }

    /**
     * Get id of latest created row.
     *
     * @return string|null
     */
    protected function _getLastInsertedId()
    {
        $objectId = null;
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);

        $tableName = $this->getObject()->getCoreTableName();
        $query = 'SELECT OXID FROM '. $tableName .' ORDER BY OXTIMESTAMP DESC LIMIT 1';
        $result = $oDb->select($query);

        if ($result != false && $result->count() > 0) {
            $fields = $result->fields;
            $objectId = $fields['OXID'];
        }

        return $objectId;
    }
}
