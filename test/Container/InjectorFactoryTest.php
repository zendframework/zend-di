<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Container;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsType;
use Zend\Di\Container\InjectorFactory;
use Psr\Container\ContainerInterface;
use Zend\Di\ConfigInterface;
use Zend\Di\InjectorInterface;

/**
 * @coversDefaultClass Zend\Di\Container\InjectorFactory
 */
class InjectorFactoryTest extends TestCase
{
    public function testFactoryIsInvokable()
    {
        $this->assertInternalType(IsType::TYPE_CALLABLE, new InjectorFactory());
    }

    public function testCreateWillReturnAnInjectorInstance()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $result = (new InjectorFactory())->create($container);

        $this->assertInstanceOf(InjectorInterface::class, $result);
    }

    public function testInvokeWillReturnAnInjectorInstance()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $factory = new InjectorFactory();
        $result = $factory($container);

        $this->assertInstanceOf(InjectorInterface::class, $result);
    }

    public function testUsesConfigServiceFromContainer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with(ConfigInterface::class)
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($configMock);

        $injector = (new InjectorFactory())->create($container);

        $reflection = new \ReflectionObject($injector);
        $property = $reflection->getProperty('config');

        $property->setAccessible(true);
        $this->assertSame($configMock, $property->getValue($injector));
    }
}
