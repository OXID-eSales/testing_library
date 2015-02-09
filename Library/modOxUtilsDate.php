<?php

/**
 * Useful for defining custom time
 */
class modOxUtilsDate extends oxUtilsDate
{

    /** @var string */
    protected $_sTime = null;

    /**
     * Returns instance of oxUtilsDate.
     *
     * @return oxUtilsDate
     */
    public static function getInstance()
    {
        return oxRegistry::get("oxUtilsDate");
    }

    /**
     * @param string $sTime
     */
    public function UNITSetTime($sTime)
    {
        $this->_sTime = $sTime;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        if (!is_null($this->_sTime)) {
            return $this->_sTime;
        }

        return parent::getTime();
    }
}
