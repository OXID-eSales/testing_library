<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 * @version       OXID eShop CE
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
