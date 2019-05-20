<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\ProjectConfiguration;

use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelper;
use OxidEsales\TestingLibrary\Services\Library\Exception\FileNotFoundException;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use OxidEsales\TestingLibrary\Services\Library\ProjectConfigurationHandler;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;

class ProjectConfiguration implements ShopServiceInterface
{
    /**
     * @var $projectConfiguration
     */
    private $projectConfiguration;

    /**
     * Initiates service requirements.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->projectConfiguration = new ProjectConfigurationHandler(new ProjectConfigurationHelper());
    }

    /**
     * Initiates service.
     *
     * @param Request $request
     */
    public function init($request)
    {
        if ($request->getParameter('backup')) {
            $this->projectConfiguration->backup();
        }

        if ($request->getParameter('restore')) {
            $this->projectConfiguration->restore();
        }

        if ($request->getParameter('cleanup')) {
            try {
                $this->projectConfiguration->cleanup();
            } catch (FileNotFoundException $exception) {}
        }
    }
}
