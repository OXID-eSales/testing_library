<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Helper class for \OxidEsales\Eshop\Core\Email
 * @deprecated since v4.0.0
 */
class oxEmailHelper extends \OxidEsales\Eshop\Core\Email
{
    /** @var bool Return value of any defined function. */
    public static $blRetValue = null;

    /** @var bool Whether email was sent to user. */
    public static $blSendToUserWasCalled = null;

    /** @var bool Whether email was sent to shop owner.  */
    public static $blSendToOwnerWasCalled = null;

    /** @var \OxidEsales\Eshop\Application\Model\Order User order used during email sending. */
    public static $oUserOrder = null;

    /** @var \OxidEsales\Eshop\Application\Model\Order Owner order used during email sending. */
    public static $oOwnerOrder = null;

    /**
     * Mocked method for testing.
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $oOrder
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
     * @param \OxidEsales\Eshop\Application\Model\Order $order
     * @param string    $subject
     *
     */
    public function sendOrderEmailToOwner($order, $subject = null)
    {
        self::$blSendToOwnerWasCalled = true;
        self::$oOwnerOrder = $order;

        return null;
    }

    /**
     * Mocked method for testing.
     *
     * @param \OxidEsales\Eshop\Application\Model\User $oUser
     * @param string $sSubject
     *
     * @return bool
     */
    public function sendNewsletterDBOptInMail($oUser, $sSubject = null)
    {
        return self::$blRetValue;
    }
}
