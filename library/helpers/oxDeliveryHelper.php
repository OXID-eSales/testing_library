<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\Delivery
 * @deprecated since v4.0.0
 */
class oxDeliveryHelper extends \OxidEsales\Eshop\Application\Model\Delivery
{
    /**
     * Cleans oxDelivery static parameters.
     */
    public static function cleanup()
    {
        self::$_aProductList = array();
    }
}
