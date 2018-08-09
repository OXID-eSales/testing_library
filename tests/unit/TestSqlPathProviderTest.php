<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\Eshop\Core\Edition\EditionSelector;
use OxidEsales\TestingLibrary\TestSqlPathProvider;

class TestSqlPathProviderTest extends PHPUnit\Framework\TestCase
{
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

    /**
     * @param string $testSuitePath
     * @param string $edition
     * @param string $resultPath
     *
     * @dataProvider providerChecksForCorrectPath
     */
    public function testChecksForCorrectPath($testSuitePath, $edition, $resultPath)
    {
        $shopPath = '/var/www/oxideshop/source';
        $editionSelector = new EditionSelector($edition);
        $testDataPathProvider = new TestSqlPathProvider($editionSelector, $shopPath);

        $this->assertSame($resultPath, $testDataPathProvider->getDataPathBySuitePath($testSuitePath));
    }
}
