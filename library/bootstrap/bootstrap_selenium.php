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
 * @copyright (C) OXID eSales AG 2003-2014
 */

class SeleniumBootstrap extends Bootstrap
{
    /** @var int Whether to add demo data when installing the shop. */
    protected $addDemoData = 1;

    /**
     * Initiates shop before testing.
     */
    public function init()
    {
        parent::init();

        define("SHOP_EDITION", ($this->getTestConfig()->getShopEdition() == 'EE') ? 'EE' : 'PE_CE');

        $this->prepareScreenShots();
        $this->copyTestFilesToShop();

        require_once TEST_LIBRARY_PATH .'/oxAcceptanceTestCase.php';
    }

    /**
     * Creates screenshots directory if it does not exists.
     */
    public function prepareScreenShots()
    {
        $screenShotsPath = $this->getTestConfig()->getScreenShotsPath();
        if ($screenShotsPath && !is_dir($screenShotsPath)) {
            mkdir($screenShotsPath, 0777, true);
        }
    }

    /**
     * Some test files are needed to successfully run selenium tests.
     * Currently only files needed for clearing cookies are copied.
     */
    public function copyTestFilesToShop()
    {
        $config = $this->getTestConfig();
        $target = $config->getRemoteDirectory() ? $config->getRemoteDirectory().'/_cc.php' : $config->getShopPath().'/_cc.php';
        $fileCopier = new oxFileCopier();
        $fileCopier->copyFiles(TEST_LIBRARY_PATH .'_cc.php', $target, true);
    }

    /**
     * Sets correct oxConfig object for selenium tests.
     */
    protected function prepareShopModObjects()
    {
        parent::prepareShopModObjects();

        oxRegistry::set("oxConfig", oxNew('oxConfig'));
    }
}
