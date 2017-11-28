<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

// Clean cookies
if (array_key_exists('HTTP_COOKIE', $_SERVER) && !empty($_SERVER['HTTP_COOKIE'])) {
    $aCookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach ($aCookies as $sCookie) {
        $sRawCookie = explode('=', $sCookie);
        setcookie(trim( $sRawCookie[0] ), '', time() - 10000, '/');
    }
    // removing sid that created by clearing cache
    setcookie( 'sid', '', time() - 10000, '/' );
}
