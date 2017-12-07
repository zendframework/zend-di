<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Di\CodeGenerator\InjectorGenerator;
use Zend\Di\Config;
use Zend\Di\ConfigInterface;
use Zend\Di\Container\GeneratorFactory;
use Zend\Di\Injector;
use Zend\Di\InjectorInterface;

/**
 * @covers Zend\Di\Container\GeneratorFactory
 *
 */
class GeneratorFactoryTest extends TestCase
{
    public function testInvokeCreatesGenerator()
    {
        $injector = new Injector();
        $factory = new GeneratorFactory();

        $result = $factory($injector->getContainer());
        $this->assertInstanceOf(InjectorGenerator::class, $result);
    }

    /**
     * Data provider for testFactoryUsesServiceFromContainer
     */
    public function provideContainerServices()
    {
        return [
            //              serviceName, provided instance
            'config'    => [ConfigInterface::class,     new Config()],
            'injector'  => [InjectorInterface::class,   new Injector()]
        ];
    }

    /**
     * @dataProvider provideContainerServices
     */
    public function testFactoryUsesServiceFromContainer(string $serviceName, $instance): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with($serviceName)
            ->willReturn(true);

        $container->method('has')->willReturn(false);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with($serviceName)
            ->willReturn($instance);

        $factory = new GeneratorFactory();
        $factory($container);
    }
}
