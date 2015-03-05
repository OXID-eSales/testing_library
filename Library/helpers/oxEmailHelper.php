<?php

/**
 * Helper class for oxEmail.
 */
class oxEmailHelper extends oxEmail
{
    /** @var bool Return value of any defined function. */
    public static $blRetValue = null;

    /** @var bool Whether email was sent to user. */
    public static $blSendToUserWasCalled = null;

    /** @var bool Whether email was sent to shop owner.  */
    public static $blSendToOwnerWasCalled = null;

    /** @var oxOrder User order used during email sending. */
    public static $oUserOrder = null;

    /** @var oxOrder Owner order used during email sending. */
    public static $oOwnerOrder = null;

    /**
     * Mocked method for testing.
     *
     * @param oxOrder $oOrder
     * @param string  $sSubject
     *
     * @return bool
     */
    public function sendOrderEmailToUser($oOrder, $sSubject = null)
    {
        self::$blSendToUserWasCalled = true;
        self::$oUserOrder = $oOrder;

        return self::$blRetValue;
    }

    /**
     * Mocked method for testing.
     *
     * @param oxOrder $oOrder
     * @param string    $sSubject
     *
     * @return null
     */
    public function sendOrderEmailToOwner($oOrder, $sSubject = null)
    {
        self::$blSendToOwnerWasCalled = true;
        self::$oOwnerOrder = $oOrder;

        return null;
    }

    /**
     * Mocked method for testing.
     *
     * @param oxUser $oUser
     * @param string $sSubject
     *
     * @return bool
     */
    public function sendNewsletterDBOptInMail($oUser, $sSubject = null)
    {
        return self::$blRetValue;
    }
}
