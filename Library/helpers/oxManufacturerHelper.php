<?php

/**
 * Helper class for oxManufacturer.
 */
class oxManufacturerHelper extends oxManufacturer
{

    /**
     * Clean classes static variables.
     */
    public static function cleanup()
    {
        self::$_aRootManufacturer = array();
    }
}