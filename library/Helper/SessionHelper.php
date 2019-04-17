<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Helper;

use OxidEsales\Eshop\Core\Session;

/**
 * @internal
 */
class SessionHelper extends Session
{
    public static function resetStaticPropertiesToDefaults() {
        static::$_blIsNewSession = false;
        static::$_oUser = null;
    }
}
