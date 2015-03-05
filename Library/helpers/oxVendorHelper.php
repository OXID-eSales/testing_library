<?php

/**
 * Helper class for oxVendor.
 */
class oxVendorHelper extends oxVendor
{
    /**
     * Cleans classes static variables.
     */
    public static function cleanup()
    {
        self::$_aRootVendor = array();
    }
}