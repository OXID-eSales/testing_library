<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ViewsGenerator;

use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Regenerates shop views
 */
class ViewsGenerator implements ShopServiceInterface
{
    /**
     * @param ServiceConfig $config
     */
    public function __construct($config) {}

    /**
     * Clears shop cache
     *
     * @param Request $request
     */
    public function init($request)
    {
        $oGenerator = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);
        $oGenerator->updateViews();
    }
}
