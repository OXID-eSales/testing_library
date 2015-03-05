<?php

/**
 * Helper class for oxSeoEncoder.
 */
class oxSeoEncoderHelper extends oxSeoEncoder
{

    /**
     * Clean classes static variables.
     */
    public static function cleanup()
    {
        self::$_aFixedCache = array();
        self::$_sCacheKey = null;
        self::$_aCache = null;
    }
}
