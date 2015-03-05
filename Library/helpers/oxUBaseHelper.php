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

/**
 * Helper class for oxUBase.
 */
class oxUBaseHelper extends oxUBase
{

    /** @var bool Was init function called. */
    public $initWasCalled = false;

    /** @var bool Was parent class called. */
    public $setParentWasCalled = false;

    /** @var bool Whether action was set. */
    public $setThisActionWasCalled = false;

    /**
     * Calls self::_processRequest(), initializes components which needs to
     * be loaded, sets current list type, calls parent::init()
     */
    public function init()
    {
        $this->initWasCalled = true;
    }

    /**
     * Cleans classes static variables.
     */
    public static function cleanup()
    {
        self::resetComponentNames();
    }

    /**
     * Sets class parent.
     *
     * @param null $oParam
     */
    public function setParent($oParam = null)
    {
        $this->setParentWasCalled = true;
    }

    /**
     * Sets action.
     *
     * @param null $oParam
     */
    public function setThisAction($oParam = null)
    {
        $this->setThisActionWasCalled = true;
    }

    /**
     * Resets collected component names.
     */
    public static function resetComponentNames()
    {
        parent::$_aCollectedComponentNames = null;
    }
}
