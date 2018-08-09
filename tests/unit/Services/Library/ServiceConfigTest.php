<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;

class ServiceConfigTest extends PHPUnit\Framework\TestCase
{

    public function testReturningDefaultShopPath()
    {
        $config = new ServiceConfig('/path/to/shop/');

        $this->assertEquals('/path/to/shop/', $config->getShopDirectory());
    }
}
