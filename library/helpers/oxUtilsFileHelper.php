<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Core\UtilsFile
 * @deprecated since v4.0.0
 */
class oxUtilsFileHelper extends \OxidEsales\Eshop\Core\UtilsFile
{
    /** @var string Value of read file */
    public static $ret = "UNLICENSED";

    /**
     * Returns $ret value.
     *
     * @param string $sPath
     * @return string
     */
    public function readRemoteFileAsString($sPath)
    {
        return self::$ret;
    }
}
