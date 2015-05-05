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

require_once 'Constructors/ConstructorFactory.php';

/**
 * Shop constructor class for modifying shop environment during testing
 * Class ShopConstructor
 */
class ShopObjectConstructor implements ShopServiceInterface
{
    /** @var ServiceConfig */
    private $serviceConfig;

    /**
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->serviceConfig = $config;
    }

    /**
     * Loads object, sets class parameters and calls function with parameters.
     * classParams can act two ways - if array('param' => 'value') is given, it sets the values to given keys
     * if array('param', 'param') is passed, values of these params are returned.
     * classParams are only returned if no function is called. Otherwise function return value is returned.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function init($request)
    {
        if (!is_null($request->getParameter('shp'))) {
            $this->setActiveShop($request->getParameter('shp'));
        }
        if (!is_null($request->getParameter('lang'))) {
            $this->setActiveLanguage($request->getParameter('lang'));
        }

        $oConstructorFactory = new ConstructorFactory();
        $oConstructor = $oConstructorFactory->getConstructor($request->getParameter("cl"));

        $oConstructor->load($request->getParameter("oxid"));

        $mResult = '';
        if ($request->getParameter('classparams')) {
            $mResult = $oConstructor->setClassParameters($request->getParameter('classparams') );
        }

        if ($request->getParameter('fnc')) {
            $mResult = $oConstructor->callFunction($request->getParameter('fnc'), $request->getParameter('functionparams'));
        }

        return $mResult;
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->serviceConfig;
    }

    /**
     * Switches active shop
     *
     * @param string $shopId
     */
    protected function setActiveShop($shopId)
    {
        if ($shopId && $this->getServiceConfig()->getShopEdition() == 'EE') {
            oxRegistry::getConfig()->setShopId($shopId);
        }
    }

    /**
     * Switches active language
     *
     * @param string $language
     *
     * @throws Exception
     */
    protected function setActiveLanguage($language)
    {
        $languages = oxRegistry::getLang()->getLanguageIds();
        $languageId = array_search($language, $languages);
        if ($languageId === false) {
            throw new Exception("Language $language was not found or is not active in shop");
        }
        oxRegistry::getLang()->setBaseLanguage($languageId);
    }
}
