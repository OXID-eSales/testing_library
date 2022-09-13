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

    public function providerChecksForCorrectPath(): array
    {
        return [
            [
                '/var/www/vendor/oxid-esales/tests-deprecated-ee/Acceptance/Admin',
                EditionSelector::ENTERPRISE,
                '/var/www/vendor/oxid-esales/tests-deprecated-ee/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/vendor/oxid-esales/tests-deprecated-ee/Acceptance/Frontend',
                EditionSelector::ENTERPRISE,
                '/var/www/vendor/oxid-esales/tests-deprecated-ee/Acceptance/Frontend/testSql'
            ],
            [
                '/var/www/vendor/oxid-esales/tests-deprecated-ce/Acceptance/Admin',
                EditionSelector::COMMUNITY,
                '/var/www/vendor/oxid-esales/tests-deprecated-ce/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/vendor/oxid-esales/tests-deprecated-pe/Acceptance/Admin',
                EditionSelector::PROFESSIONAL,
                '/var/www/vendor/oxid-esales/tests-deprecated-pe/Acceptance/Admin/testSql'
            ],
        ];
    }
}
