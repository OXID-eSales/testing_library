<?php

/**
 * Helper class for oxAdminView.
 */
class oxAdminViewHelper extends oxAdminView
{

    /**
     * Cleans oxAdminView static cache.
     */
    public static function cleanup()
    {
        self::$_sAuthUserRights = null;
    }
}
