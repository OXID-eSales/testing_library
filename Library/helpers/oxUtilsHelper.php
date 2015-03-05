<?php

/**
 * Helper class for oxUtils.
 */
class oxUtilsHelper extends oxUtils
{
    /** @var null Redirect url. */
    public static $sRedirectUrl = null;

    /** @var bool Should SEO engine be active during testing. */
    public static $sSeoIsActive = false;

    /** @var bool Should shop act as a search engine during testing. */
    public static $blIsSearchEngine = false;

    /**
     * Rewrites parent::redirect method.
     *
     * @param string $sUrl
     * @param bool   $blAddRedirectParam
     * @param int    $iHeaderCode
     *
     * @return null
     */
    public function redirect($sUrl, $blAddRedirectParam = true, $iHeaderCode = 301)
    {
        self::$sRedirectUrl = $sUrl;
    }

    /**
     * Rewrites parent::seoIsActive method.
     *
     * @param bool $blReset
     * @param null $sShopId
     * @param null $iActLang
     *
     * @return bool
     */
    public function seoIsActive($blReset = false, $sShopId = null, $iActLang = null)
    {
        return self::$sSeoIsActive;
    }

    /**
     * Rewrites parent::isSearchEngine method.
     *
     * @param bool $blReset
     * @param null $sShopId
     * @param null $iActLang
     * @return bool
     */
    public function isSearchEngine($blReset = false, $sShopId = null, $iActLang = null)
    {
        return self::$blIsSearchEngine;
    }
}
