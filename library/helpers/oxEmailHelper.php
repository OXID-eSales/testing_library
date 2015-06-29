<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

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
     * @param oxOrder $order
     * @param string    $subject
     *
     * @return null
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
