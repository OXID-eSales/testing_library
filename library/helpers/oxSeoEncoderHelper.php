<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Core\SeoEncoder
 * @deprecated since v4.0.0
 */
class oxSeoEncoderHelper extends \OxidEsales\Eshop\Core\SeoEncoder
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
