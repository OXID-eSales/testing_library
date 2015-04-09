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

use org\bovigo\vfs\vfsStream;

require_once ROOT_DIRECTORY .'library/Services/Library/ServiceConfig.php';
require_once ROOT_DIRECTORY .'library/Services/Library/Request.php';
require_once ROOT_DIRECTORY .'library/Services/ServiceCaller.php';

class ServiceCallerTest extends PHPUnit_Framework_TestCase
{
    public function testCreationOfService()
    {
        $structure = array(
            'Services' => array(
                'TestService' => array(
                    'TestService.php' => '<?php
                    class TestService implements ShopServiceInterface {
                        public function __construct($config) {}
                        public function init($request) {}
                    }'
                )
            )
        );

        vfsStream::setup('root', 777, $structure);

        /** @var ServiceConfig|PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMock('ServiceConfig', array('getServicesDirectory', 'getShopDirectory'));
        $config->expects($this->any())->method('getServicesDirectory')->will($this->returnValue(vfsStream::url('root/Services')));
        $config->expects($this->any())->method('getShopDirectory')->will($this->returnValue(vfsStream::url('root')));

        $request = new Request();

        $serviceCaller = new ServiceCaller($config);
        $serviceCaller->callService('TestService', $request);
    }

    public function testThrowingExceptionWhenServiceNotFound()
    {
        $message = "Service TestService not found in path vfs://root/TestService/TestService.php!";
        $this->setExpectedException('Exception', $message);

        vfsStream::setup('root', 777);

        /** @var ServiceConfig|PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMock('ServiceConfig', array('getServicesDirectory', 'getShopDirectory'));
        $config->expects($this->any())->method('getServicesDirectory')->will($this->returnValue(vfsStream::url('root')));
        $config->expects($this->any())->method('getShopDirectory')->will($this->returnValue(vfsStream::url('root')));

        $request = new Request();

        $serviceCaller = new ServiceCaller($config);
        $serviceCaller->callService('TestService', $request);
    }

}
