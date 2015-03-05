<?php

/**
 * Helper class for oxVoucher.
 */
class oxVoucherHelper extends oxVoucher
{
    /** @var bool Whether any of the checks were performed. */
    public static $blCheckWasPerformed = false;

    /**
     * Checks availability without user logged in. Returns array with errors.
     *
     * @param array  $aVouchers array of vouchers
     * @param double $dPrice    current sum (price)
     *
     * @return array
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
     *
     * @return array
     */
    public function checkBasketVoucherAvailability($aVouchers, $dPrice)
    {
        self::$blCheckWasPerformed = true;
    }

    /**
     * Checks availability for the given user. Returns array with errors.
     *
     * @param object $oUser user object
     *
     * @return array
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
