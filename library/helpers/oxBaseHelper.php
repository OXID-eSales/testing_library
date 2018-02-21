<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Core\Model\BaseModel
 * @deprecated since v4.0.0
 */
class oxBaseHelper extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /**
     * Clears class static variables.
     */
    public static function cleanup()
    {
        \OxidEsales\Eshop\Core\Model\BaseModel::$_blDisableFieldCaching = array();
    }
}
