<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\ThemeSwitcher;

class ThemeSwitcher implements \OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface
{

    protected $currentThemeName = '';

    /**
     * ThemeService constructor.
     *
     * @param \OxidEsales\TestingLibrary\Services\Library\ServiceConfig $config
     */
    public function __construct($config)
    {
    }

    /**
     * Initiates service.
     *
     * @param \OxidEsales\TestingLibrary\Services\Library\Request $request
     */
    public function init($request)
    {
        $themeName = $request->getParameter('themeName');
        $shopId = $request->getParameter('shp');

        $currentShopId = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
        \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($shopId);

        $theme = oxNew( \OxidEsales\Eshop\Core\Theme::class);
        $theme->load($themeName);
        $theme->activate();

        \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($currentShopId);
    }
}
