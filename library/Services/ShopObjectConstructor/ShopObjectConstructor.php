<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\ShopObjectConstructor;

use Exception;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use OxidEsales\TestingLibrary\Services\ShopObjectConstructor\Constructor\ConstructorFactory;


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
            $mResult = $oConstructor->setClassParameters($request->getParameter('classparams'));
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
            \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($shopId);
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
        $languages = \OxidEsales\Eshop\Core\Registry::getLang()->getLanguageIds();
        $languageId = array_search($language, $languages);
        if ($languageId === false) {
            throw new Exception("Language $language was not found or is not active in shop");
        }
        \OxidEsales\Eshop\Core\Registry::getLang()->setBaseLanguage($languageId);
    }
}
