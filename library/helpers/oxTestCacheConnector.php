<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
