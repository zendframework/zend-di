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

    public function testFactoryUsesServicesFromContainer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with(InjectorInterface::class)
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with(ConfigInterface::class)
            ->willReturn(true);

        $container->method('has')->willReturn(false);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with(InjectorInterface::class)
            ->willReturn(new Injector());

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn(new Config());

        $factory = new GeneratorFactory();
        $factory($container);
    }
}
