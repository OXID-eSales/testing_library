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
namespace OxidEsales\TestingLibrary\DatabaseRestorer;

use Exception;

/**
 * Factory for DatabaseRestorer.
 */
class DatabaseRestorerFactory
{
    /**
     * Creates and returns database restoration object.
     *
     * @param $className
     * @return mixed
     * @throws Exception
     */
    public function createRestorer($className)
    {
        if (!class_exists($className)) {
            $className = __NAMESPACE__ . '\\' . $className;
        }

        $restorer = class_exists($className) ? new $className : new DatabaseRestorer();

        if (!($restorer instanceof DatabaseRestorerInterface)) {
            throw new Exception("Database restorer class should implement DatabaseRestorerInterface interface!");
        }

        return $restorer;
    }
}
