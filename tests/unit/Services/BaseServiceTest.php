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

namespace OxidEsales\TestingLibrary\Unit\Services;

use PHPUnit_Framework_TestCase;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\ClearCache\ClearCache;
use OxidEsales\TestingLibrary\Services\ModuleInstaller\ModuleInstaller;

/**
 * Tests for the abstract service classes.
 */
class BaseServiceTest extends PHPUnit_Framework_TestCase
{
    public function testNeedBootstrapCaseFalse()
    {
        $exampleService = new ClearCache($this->createExampleServiceConfig());

        $this->assertFalse($exampleService->needBootstrap());
    }

    public function testNeedBootstrapCaseTrue()
    {
        $exampleService = new ModuleInstaller($this->createExampleServiceConfig());

        $this->assertTrue($exampleService->needBootstrap());
    }

    /**
     * @return ServiceConfig An example service configuration object.
     */
    protected function createExampleServiceConfig()
    {
        return new ServiceConfig('/path/to/shop/');
    }
}
