<?php

/**
 * Helper class for oxNewsSubscribed.
 */
class oxNewsSubscribedHelper extends oxNewsSubscribed
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
