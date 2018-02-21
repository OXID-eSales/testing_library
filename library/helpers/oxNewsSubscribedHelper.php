<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Application\Model\NewsSubscribed
 * @deprecated since v4.0.0
 */
class oxNewsSubscribedHelper extends \OxidEsales\Eshop\Application\Model\NewsSubscribed
{

    /**
     * Sets whether user was subscribed.
     *
     * @param bool $wasSubscribed
     */
    public function setWasSubscribed($wasSubscribed)
    {
        $this->_blWasSubscribed = $wasSubscribed;
    }

    /**
     * Returns whether user was subscribed.
     *
     * @return bool
     */
    public function getWasSubscribed()
    {
        return $this->_blWasSubscribed;
    }
}
