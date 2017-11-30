<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Container\ServiceManager;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Di\Container\AutowireFactory as GenericAutowireFactory;
use Zend\Di\Container\ServiceManager\AutowireFactory;
use stdClass;

/**
 * AutowireFactory test case.
 *
 * @coversDefaultClass Zend\Di\Container\ServiceManager\AutowireFactory
 */
class AutowireFactoryTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createGenericFactoryMock()
    {
        return $this->getMockBuilder(GenericAutowireFactory::class)
            ->setMethodsExcept()
            ->getMock();
    }

    public function testInvokeIsPassedToGenericFactory()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $mock = $this->createGenericFactoryMock();
        $expected = new stdClass();
        $className = 'AnyClassName';

        // Container must not be called directly
        $container->expects($this->never())->method('has');
        $container->expects($this->never())->method('get');

        $mock->expects($this->once())
            ->method('create')
            ->with($container, $className)
            ->willReturn($expected);

        $factory = new AutowireFactory($mock);

        $this->assertSame($expected, $factory($container, $className));
    }

    public function testCanCreateIsPassedToGenericFactory()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $mock = $this->createGenericFactoryMock();
        $className = 'AnyClassName';

        // Container must not be called directly
        $container->expects($this->never())->method('has');
        $container->expects($this->never())->method('get');

        $mock->expects($this->once())
            ->method('canCreate')
            ->with($container, $className)
            ->willReturn(true);

        $factory = new AutowireFactory($mock);

        $this->assertTrue($factory->canCreate($container, $className));
    }
}
