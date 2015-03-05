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
 * This script clears shop cache
 */
class ClearCache implements ShopServiceInterface
{
    /**
     * Clears shop cache
     */
    public function init()
    {
        // Clean tmp
        if ($sCompileDir = oxRegistry::get('oxConfigFile')->getVar('sCompileDir')) {
            $this->removeDirectory($sCompileDir, true);
        }

        if (OXID_VERSION_EE) :
            if (class_exists('oxReverseProxyBackEnd')) {
                // Clean cache
                if ($sCacheDir = oxRegistry::get('oxConfigFile')->getVar('sCacheDir')) {
                    $this->removeDirectory($sCacheDir, true);
                }

                // Flush cache
                $oCache = oxNew('oxCacheBackend');
                $oCache->flush();

                // Invalidate reverse cache
                $oReverseProxy = oxNew('oxReverseProxyBackEnd');
                $oReverseProxy->setFlush();
                $oReverseProxy->execute();
            }
        endif;
    }

    /**
     * Delete all files and dirs recursively
     *
     * @param string $sDir directory to delete
     * @param bool $blKeepTargetDir keep target directory
     *
     * @return null
     */
    protected function removeDirectory($sDir, $blKeepTargetDir = false)
    {
        $aFiles = array_diff( scandir( $sDir ), array('.', '..') );
        foreach ($aFiles as $sFile) {
            ( is_dir( "$sDir/$sFile" ) ) ? $this->removeDirectory( "$sDir/$sFile", false ) : @unlink( "$sDir/$sFile" );
        }
        if ( !$blKeepTargetDir ) {
            @rmdir( $sDir );
        }
    }
}


