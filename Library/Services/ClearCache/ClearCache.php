<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
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


