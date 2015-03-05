<?php

/**
 * Helper class for oxCache.
 */
class oxCacheHelper extends oxCache
{
    /**
     * Throw an exception on reset.
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
