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
namespace OxidEsales\TestingLibrary\Services\SubShopHandler;

use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Assigns items to subshop
 */
class SubShopHandler implements ShopServiceInterface
{
    /**
     * @param ServiceConfig $serviceConfiguration
     */
    public function __construct($serviceConfiguration) {}

    /**
     * Assigns element to subshop
     *
     * @param Request $request
     */
    public function init($request)
    {
        $sElementTable = $request->getParameter("elementtable");
        $sShopId = $request->getParameter("shopid");
        $sParentShopId = $request->getParameter("parentshopid");
        $sElementId = $request->getParameter("elementid");
        if ($sElementId) {
            $this->assignElementToSubShop($sElementTable, $sShopId, $sElementId);
        } else {
            $this->assignAllElementsToSubShop($sElementTable, $sShopId, $sParentShopId);
        }
    }

    /**
     * Defines if service require OXID eShop bootstrap.
     *
     * @return bool
     */
    public function needBootstrap()
    {
        return true;
    }

    /**
     * Assigns element to subshop
     *
     * @param string  $sElementTable Name of element table
     * @param integer $sShopId       Subshop id
     * @param integer $sElementId    Element id
     */
    public function assignElementToSubShop($sElementTable, $sShopId, $sElementId)
    {
        /** @var BaseModel $oBase */
        $oBase = oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
        $oBase->init($sElementTable);
        if ($oBase->load($sElementId)) {
            /** @var \OxidEsales\Eshop\Core\Element2ShopRelations $oElement2ShopRelations */
            $oElement2ShopRelations = oxNew(\OxidEsales\Eshop\Core\Element2ShopRelations::class, $sElementTable);
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
     */
    public function assignAllElementsToSubShop($sElementTable, $sShopId, $sParentShopId = 1)
    {
        /** @var \OxidEsales\Eshop\Core\Element2ShopRelations $oElement2ShopRelations */
        $oElement2ShopRelations = oxNew(\OxidEsales\Eshop\Core\Element2ShopRelations::class, $sElementTable);
        $oElement2ShopRelations->setShopIds($sShopId);
        $oElement2ShopRelations->inheritFromShop($sParentShopId);
    }
}
