<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Helper;

use OxidEsales\Facts\Facts;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
class ProjectConfigurationHelper implements ProjectConfigurationHelperInterface
{
    /**
     * @return string
     */
    public function getConfigurationDirectoryPath(): string
    {
        return Path::join((new Facts())->getShopRootPath(), '/var/configuration/');
    }
}
