<?php

/**
 * Helper class for oxUtilsFile.
 */
class oxUtilsFileHelper extends oxUtilsFile
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
