<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;

/**
 * Class oxConfigCaller
 */
class oxBaseConstructor extends ObjectConstructor
{
    /**
     * Initiates object instead of loading it
     *
     * @param string $objectId
     *
     * @return mixed
     */
    protected function _loadById($objectId)
    {
        return $this->getObject()->init($objectId);
    }
}
