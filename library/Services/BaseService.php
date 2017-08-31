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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\TestingLibrary\Services;

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;

/**
 * Abstract base class for every service. This should be the root of every service inheritance tree.
 */
abstract class BaseService implements ShopServiceInterface
{
    /** @var ServiceConfig */
    protected $serviceConfig;

    /**
     * @var bool Is the bootstrap needed for this service?
     */
    protected $bootstrapNeeded = false;

    /**
     * @param ServiceConfig $serviceConfiguration
     */
    public function __construct($serviceConfiguration)
    {
        $this->serviceConfig = $serviceConfiguration;
    }

    /**
     * @return ServiceConfig
     */
    protected function getServiceConfig()
    {
        return $this->serviceConfig;
    }

    /**
     * Defines if service require OXID eShop bootstrap.
     *
     * @return bool
     */
    public function needBootstrap()
    {
        return $this->bootstrapNeeded;
    }
}
