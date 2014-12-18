<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

// Clean cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $aCookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach ($aCookies as $sCookie) {
        $sRawCookie = explode('=', $sCookie);
        setcookie(trim( $sRawCookie[0] ), '', time() - 10000, '/');
    }
    // removing sid that created by clearing cache
    setcookie( 'sid', '', time() - 10000, '/' );
}
