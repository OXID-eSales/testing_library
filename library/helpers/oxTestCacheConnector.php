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
 * Class oxTestCacheConnector
 */
class oxTestCacheConnector implements \OxidEsales\Eshop\Application\Model\Contract\CacheConnectorInterface
{
    /** @var array Cached items. */
    public $aCache = array();

    /**
     * Returns whether this cache connector is available.
     *
     * @return bool
     */
    public static function isAvailable()
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (!self::isAvailable()) {
            throw new Exception('CONNECTOR_NOT_AVAILABLE');
        }
    }

    /**
     * Adds item to cache.
     *
     * @param array|string $mKey
     * @param mixed        $mValue
     * @param int          $iTTL
     *
     */
    public function set($mKey, $mValue = null, $iTTL = null)
    {
        if (is_array($mKey)) {
            $this->aCache = array_merge($this->aCache, $mKey);
        } else {
            $this->aCache[$mKey] = $mValue;
        }
    }

    /**
     * Returns cached item value.
     *
     * @param array|string $mKey
     * @return array
     */
    public function get($mKey)
    {
        if (is_array($mKey)) {
            return array_intersect_key($this->aCache, array_flip($mKey));
        } else {
            return $this->aCache[$mKey];
        }
    }

    /**
     * Invalidates item's cache.
     *
     * @param array|string $mKey
     *
     */
    public function invalidate($mKey)
    {
        if (is_array($mKey)) {
            $this->aCache = array_diff_key($this->aCache, array_flip($mKey));
        } else {
            $this->aCache[$mKey] = null;
        }

    }

    /**
     * Clears cache
     */
    public function flush()
    {
        $this->aCache = array();
    }
}
