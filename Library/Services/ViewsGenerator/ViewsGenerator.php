<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

/**
 * This script clears shop cache
 */
class ViewsGenerator implements ShopServiceInterface
{
    /**
     * Clears shop cache
     */
    public function init()
    {
        $oGenerator = oxNew('oxDbMetaDataHandler');
        $oGenerator->updateViews();
    }
}


