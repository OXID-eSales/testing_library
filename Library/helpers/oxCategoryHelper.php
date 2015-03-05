<?php

/**
 * Helper class for oxCategory.
 */
class oxCategoryHelper extends oxCategory
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
