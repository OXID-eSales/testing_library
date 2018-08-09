<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use org\bovigo\vfs\vfsStream;

use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;
use OxidEsales\TestingLibrary\Services\ServiceFactory;

class ServiceFactoryTest extends PHPUnit\Framework\TestCase
{
    public function testThrowingExceptionWhenServiceNotFound()
    {
        $message = "Service 'TestService' was not found!";
        $this->setExpectedException('Exception', $message);

        vfsStream::setup('root', 777, array('bootstrap.php' => ''));

        /** @var ServiceConfig|PHPUnit\Framework\MockObject\MockObject $config */
        $config = $this->getMock('OxidEsales\TestingLibrary\Services\Library\ServiceConfig', array('getServicesDirectory', 'getShopDirectory'), [], '', false);
        $config->expects($this->any())->method('getServicesDirectory')->will($this->returnValue(vfsStream::url('root')));
        $config->expects($this->any())->method('getShopDirectory')->will($this->returnValue(vfsStream::url('root')));

        $serviceFactory = new ServiceFactory($config);
        $serviceFactory->createService('TestService');
    }
}
