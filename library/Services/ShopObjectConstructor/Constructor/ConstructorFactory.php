<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor;

/**
 * Class CallerFactory
 */
class ConstructorFactory
{
    /**
     * @param string $sClassName
     *
     * @return ObjectConstructor
     */
    public function getConstructor($sClassName)
    {
        $constructorClassName = $this->formConstructorClass($sClassName);
        if (!class_exists($constructorClassName)) {
            $constructorClassName = $this->formConstructorClass('Object');
        }

        return new $constructorClassName($sClassName);
    }

    /**
     * @param string $className
     *
     * @return bool|string
     */
    protected function formConstructorClass($className)
    {
        $sConstructorClass = $className . "Constructor";
        return "OxidEsales\\TestingLibrary\\Services\\ShopObjectConstructor\\Constructor\\$sConstructorClass";
    }
}
