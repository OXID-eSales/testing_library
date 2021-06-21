<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\TestingLibrary\Unit;

use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\TestingLibrary\TestSqlPathProvider;
use PHPUnit\Framework\TestCase;

final class TestSqlPathProviderTest extends TestCase
{
    /**
     * @dataProvider providerChecksForCorrectPath
     */
    public function testGetDataPathBySuitePath(string $testSuitePath, string $edition, string $resultPath): void
    {
        $shopPath = '/var/www/oxideshop/source';
        $editionSelectorMock = $this->createMock(EditionSelector::class);
        $editionSelectorMock->method('isEnterprise')
            ->willReturn($edition === EditionSelector::ENTERPRISE);
        $testDataPathProvider = new TestSqlPathProvider($editionSelectorMock, $shopPath);

        $datPath = $testDataPathProvider->getDataPathBySuitePath($testSuitePath);

        $this->assertSame($resultPath, $datPath);
    }

    public function providerChecksForCorrectPath()
    {
        return [
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::ENTERPRISE,
                '/var/www/oxideshop/vendor/oxid-esales/oxideshop-ee/Tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/source/Edition/Enterprise/Tests/Acceptance/Admin',
                EditionSelector::ENTERPRISE,
                '/var/www/oxideshop/source/Edition/Enterprise/Tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::COMMUNITY,
                '/var/www/oxideshop/tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::PROFESSIONAL,
                '/var/www/oxideshop/tests/Acceptance/Admin/testSql'
            ],
        ];
    }
}
