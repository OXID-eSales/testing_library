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
namespace OxidEsales\TestingLibrary\Services\Library;



/**
 * Class used for uploading files in services.
 */
class Cache
{
    /**
     * Clears cache backend.
     */
    public function clearCacheBackend()
    {
        if (class_exists('\OxidEsales\EshopEnterprise\Core\Cache\Generic\Cache')) {
            $oCache = oxNew(\OxidEsales\Eshop\Core\Cache\Generic\Cache::class);
            $oCache->flush();
        }
    }

    /**
     * Clears reverse proxy cache.
     */
    public function clearReverseProxyCache()
    {
        if (class_exists('\OxidEsales\EshopEnterprise\Core\Cache\ReverseProxy\ReverseProxyBackend')) {
            $oReverseProxy = oxNew(\OxidEsales\EshopEnterprise\Core\Cache\ReverseProxy\ReverseProxyBackend::class);
            $oReverseProxy->setFlush();
            $oReverseProxy->execute();
        }
    }

    /**
     * Clears temporary directory.
     */
    public function clearTemporaryDirectory()
    {
        if ($sCompileDir = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class)->getVar('sCompileDir')) {
            CliExecutor::executeCommand("sudo chmod 777 -R $sCompileDir");
            $this->removeTemporaryDirectory($sCompileDir, false);
        }
    }

    /**
     * Delete all files and dirs recursively
     *
     * @param string $dir       Directory to delete
     * @param bool   $rmBaseDir Keep target directory
     */
    private function removeTemporaryDirectory($dir, $rmBaseDir = false)
    {
        $itemsToIgnore = array('.', '..', '.htaccess');

        $files = array_diff(scandir($dir), $itemsToIgnore);
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $this->removeTemporaryDirectory(
                    "$dir/$file",
                    $file == 'smarty' ? $rmBaseDir : true
                );
            } else {
                @unlink("$dir/$file");
            }
        }
        if ($rmBaseDir) {
            @rmdir($dir);
        }
    }
}
