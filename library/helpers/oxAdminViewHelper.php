<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Controller\Admin\AdminController
 * @deprecated since v4.0.0
 */
class oxAdminViewHelper extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /**
     * Cleans oxAdminView static cache.
     */
    public static function cleanup()
    {
        self::$_sAuthUserRights = null;
    }
}
