<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\Vendor
 */
class oxVendorHelper extends \OxidEsales\Eshop\Application\Model\Vendor
{
    /**
     * Cleans classes static variables.
     */
    public static function cleanup()
    {
        self::$_aRootVendor = array();
    }
}
