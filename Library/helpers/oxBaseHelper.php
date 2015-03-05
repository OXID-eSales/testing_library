<?php

/**
 * Helper class for oxBase.
 */
class oxBaseHelper extends oxBase
{
    /**
     * Clears class static variables.
     */
    public static function cleanup()
    {
        oxBase::$_blDisableFieldCaching = array();
    }
}
