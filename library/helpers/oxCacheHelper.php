<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\EshopEnterprise\Core\Cache\DynamicContent\ContentCache
 * @deprecated since v4.0.0
 */
class oxCacheHelper extends \OxidEsales\EshopEnterprise\Core\Cache\DynamicContent\ContentCache
{
    /**
     * Throw an exception on reset.
     *
     * @param bool $blResetFileCache
     *
     * @throws Exception
     */
    public function reset($blResetFileCache = true)
    {
        throw new Exception('xxx', 111);
    }

    /**
     * Throw an exception on resetOn.
     *
     * @param array $resetOn reset conditions array
     * @param bool  $useAnd  reset precise level (AND conditions SQL)
     *
     * @throws Exception
     */
    public function resetOn($resetOn, $useAnd = false)
    {
        throw new Exception(serialize($resetOn));
    }
}
