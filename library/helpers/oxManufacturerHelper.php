<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\Manufacturer
 */
class oxManufacturerHelper extends \OxidEsales\Eshop\Application\Model\Manufacturer
{

    /**
     * Clean classes static variables.
     */
    public static function cleanup()
    {
        self::$_aRootManufacturer = array();
    }
}
