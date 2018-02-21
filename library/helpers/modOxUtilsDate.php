<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Useful for defining custom time
 * @deprecated since v4.0.0
 */
class modOxUtilsDate extends \OxidEsales\Eshop\Core\UtilsDate
{
    /** @var string */
    protected $_sTime = null;

    /**
     * @param string $sTime
     *
     * @deprecated Still used for old tests to work. Use setTime instead.
     */
    public function UNITSetTime($sTime)
    {
        $this->setTime($sTime);
    }

    /**
     * @param string $sTime
     */
    public function setTime($sTime)
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
