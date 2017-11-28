<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer;

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
