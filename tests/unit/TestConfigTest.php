<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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