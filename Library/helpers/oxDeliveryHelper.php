<?php

/**
 * Helper class for oxDelivery.
 */
class oxDeliveryHelper extends oxDelivery
{
    /**
     * Cleans oxDelivery static parameters.
     */
    public static function cleanup()
    {
        self::$_aProductList = array();
    }
}