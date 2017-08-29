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
 * @copyright (C) OXID eSales AG 2003-2016
 */

namespace OxidEsales\TestingLibrary\Services\Files;

use OxidEsales\TestingLibrary\Services\BaseService;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\Library\ShopServiceInterface;
use Symfony\Component\Filesystem\Filesystem;

class Remove extends BaseService implements ShopServiceInterface
{
    const FILES_PARAMETER_NAME = 'files';

    /** @var Filesystem */
    private $fileSystem;

    /**
     * Remove constructor.
     * @param ServiceConfig $serviceConfiguration
     */
    public function __construct($serviceConfiguration)
    {
        parent::__construct($serviceConfiguration);

        $this->fileSystem = new Filesystem();
    }

    /**
     * @param \OxidEsales\TestingLibrary\Services\Library\Request $request
     */
    public function init($request)
    {
        $this->fileSystem->remove($request->getParameter(static::FILES_PARAMETER_NAME));
    }
}
