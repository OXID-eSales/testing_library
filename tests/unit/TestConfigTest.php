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
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\TestingLibrary\Unit;

use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase;

/**
 * Class TestConfigTest
 *
 * @package OxidEsales\TestingLibrary\Unit
 * @covers \OxidEsales\TestingLibrary\TestConfig
 */
class TestConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers \OxidEsales\TestingLibrary\TestConfig::getModuleTestSuites()
     */
    public function testGetModuleTestSuites()
    {
        $this->buildModuleStructureWithTwoModules();

        $testConfig = $this->getMock(
            '\OxidEsales\TestingLibrary\TestConfig',
            [
                'shouldRunModuleTests',
                'getPartialModulePaths',
                'getShopPath'

            ]
        );
        $testConfig->expects($this->any())->method('shouldRunModuleTests')->will($this->returnValue(true));
        $testConfig->expects($this->any())->method('getPartialModulePaths')->will(
            $this->returnValue(
                [
                    'myvendor/namespacedModule',
                    'myvendor/plainModule'
                ]
            )
        );

        $shopPath = vfsStream::url('root/');
        $testConfig->expects($this->any())->method('getShopPath')->will($this->returnValue($shopPath));

        $this->assertEquals(
            [
                vfsStream::url('root/modules/myvendor/namespacedModule/Tests/'),
                vfsStream::url('root/modules/myvendor/plainModule/tests/')
            ],
            $testConfig->getModuleTestSuites(),
            "Directories for modules test suites are not found properly."

        );
    }

    private function buildModuleStructureWithTwoModules()
    {
        $structure = [
            'modules' => [
                'myvendor' => [
                    'namespacedModule' => [
                        'Tests' => [
                            'Acceptance'
                        ]
                    ],
                    'plainModule'      => [
                        'tests' => [
                            'Acceptance'
                        ]
                    ]
                ]
            ]
        ];
        vfsStream::setup('root', null, $structure);
    }

}