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
 * Helper class for \OxidEsales\Eshop\Application\Model\Voucher
 */
class oxVoucherHelper extends  \OxidEsales\Eshop\Application\Model\Voucher
{
    /** @var bool Whether any of the checks were performed. */
    public static $blCheckWasPerformed = false;

    /**
     * Checks availability without user logged in. Returns array with errors.
     *
     * @param array  $aVouchers array of vouchers
     * @param double $dPrice    current sum (price)
     */
    public function checkVoucherAvailability($aVouchers, $dPrice)
    {
        self::$blCheckWasPerformed = true;
    }

    /**
     * Performs basket level voucher availability check (no need to check if voucher
     * is reserved or so).
     *
     * @param array  $aVouchers array of vouchers
     * @param double $dPrice    current sum (price)
     */
    public function checkBasketVoucherAvailability($aVouchers, $dPrice)
    {
        self::$blCheckWasPerformed = true;
    }

    /**
     * Checks availability for the given user. Returns array with errors.
     *
     * @param object $oUser user object
     */
    public function checkUserAvailability($oUser)
    {
        self::$blCheckWasPerformed = true;
    }

    /**
     * Mark voucher as reserved
     */
    public function markAsReserved()
    {
        self::$blCheckWasPerformed = true;
    }
}
