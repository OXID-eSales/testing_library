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
