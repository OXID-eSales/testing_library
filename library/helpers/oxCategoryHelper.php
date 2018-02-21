<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\Category
 * @deprecated since v4.0.0
 */
class oxCategoryHelper extends \OxidEsales\Eshop\Application\Model\Category
{

    /**
     * Sets the CACHE array for the oxCategory instance
     * (without it you can't set values to the static variables)
     *
     * @param array $aCache
     */
    public static function setAttributeCache($aCache = array())
    {
        self::$_aCatAttributes = $aCache;
    }
}
