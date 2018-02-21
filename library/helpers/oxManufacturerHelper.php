<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\Manufacturer
 * @deprecated since v4.0.0
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
