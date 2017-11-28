<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library;

/**
 * Interface ShopServiceInterface
 */
interface ShopServiceInterface
{
    /**
     * Initiates service requirements.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config);

    /**
     * Initiates service.
     *
     * @param Request $request
     */
    public function init($request);
}
