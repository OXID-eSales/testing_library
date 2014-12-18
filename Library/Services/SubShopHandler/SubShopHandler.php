<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

/**
 * This script clears shop cache
 */
class SubShopHandler implements ShopServiceInterface
{
    /**
     * Assigns element to subshop
     */
    public function init()
    {
        $oxConfig = oxRegistry::getConfig();
        $sElementTable = $oxConfig->getRequestParameter("elementtable");
        $sShopId = $oxConfig->getRequestParameter("shopid");
        $sParentShopId = $oxConfig->getRequestParameter("parentshopid");
        $sElementId = $oxConfig->getRequestParameter("elementid");
        if ( $sElementId ) {
            $this->assignElementToSubShop($sElementTable, $sShopId, $sElementId);
        } else {
            $this->assignAllElementsToSubShop($sElementTable, $sShopId, $sParentShopId);
        }
    }

    /**
     * Assigns element to subshop
     *
     * @param string  $sElementTable Name of element table
     * @param integer $sShopId       Subshop id
     * @param integer $sElementId    Element id
     *
     * @return null
     */
    public function assignElementToSubShop($sElementTable, $sShopId, $sElementId)
    {
        $oBase = new oxBase();
        $oBase->init($sElementTable);
        if ( $oBase->load($sElementId) ) {
            $oElement2ShopRelations = new oxElement2ShopRelations($sElementTable);
            $oElement2ShopRelations->setShopIds($sShopId);
            $oElement2ShopRelations->addToShop($oBase->getId());
        }
    }

    /**
     * Assigns element to subshop
     *
     * @param string  $sElementTable Name of element table
     * @param integer $sShopId       Subshop id
     * @param integer $sParentShopId Parent subshop id
     *
     * @return null
     */
    public function assignAllElementsToSubShop($sElementTable, $sShopId, $sParentShopId = 1)
    {
        $oElement2ShopRelations = new oxElement2ShopRelations($sElementTable);
        $oElement2ShopRelations->setShopIds($sShopId);
        $oElement2ShopRelations->inheritFromShop($sParentShopId);
    }


}


