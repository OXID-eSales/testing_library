<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Setup\Utilities;
use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\Facts\Facts;
use Webmozart\PathUtil\Path;

/**
 * Class responsible for providing path to testData directory.
 */
class TestSqlPathProvider
{
    const TEST_SQL_DIRECTORY = 'testSql';

    const TESTS_DIRECTORY = 'tests';

    const ACCEPTANCE_DIRECTORY = 'Acceptance';

    /**
     * @var EditionSelector
     */
    private $editionSelector;

    /**
     * @var string
     */
    private $shopPath = '';

    /**
     * @param EditionSelector $editionSelector
     * @param string $shopPath
     */
    public function __construct($editionSelector, $shopPath)
    {
        $this->editionSelector = $editionSelector;
        $this->shopPath = $shopPath;
    }

    /**
     * Method returns path to test data according edition.
     *
     * @param string $testSuitePath
     *
     * @return string
     */
    public function getDataPathBySuitePath($testSuitePath)
    {
        $pathToTestData = $testSuitePath;
        if ($this->getEditionSelector()->isEnterprise()) {
            $pathToTestData = $this->updatePathToTestSql($testSuitePath);
        }

        return $pathToTestData . '/' . static::TEST_SQL_DIRECTORY;
    }

    /**
     * @return EditionSelector
     */
    protected function getEditionSelector()
    {
        return $this->editionSelector;
    }

    /**
     * @return string
     */
    protected function getShopPath()
    {
        return $this->shopPath;
    }

    /**
     * Updates provided path for enterprise edition.
     *
     * @param string $pathToTestSql
     *
     * @return string
     */
    protected function updatePathToTestSql($pathToTestSql)
    {
        $pathParts = explode(static::TESTS_DIRECTORY . '/' . static::ACCEPTANCE_DIRECTORY, $pathToTestSql);
        if (count($pathParts) > 1) {
            /** @var Utilities $utilities */
            $utilities = Registry::get(Utilities::class);
            $testDirectoryName = $pathParts[count($pathParts) - 1];
            $pathToEditionTestDirectory = Path::join(
                $utilities->getRootDirectory(),
                ucfirst(static::TESTS_DIRECTORY),
                ucfirst(static::ACCEPTANCE_DIRECTORY),
                $testDirectoryName
            );

            $pathToTestSql = realpath($pathToEditionTestDirectory);
        }

        return $pathToTestSql;
    }
}
