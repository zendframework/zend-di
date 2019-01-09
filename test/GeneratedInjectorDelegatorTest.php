<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Di\GeneratedInjectorDelegator;
use Zend\Di\InjectorInterface;

class GeneratedInjectorDelegatorTest extends TestCase
{
    public function testGeneratedInjectorDoesNotExist()
    {
        $injector = $this->prophesize(InjectorInterface::class)->reveal();
        $callback = function () use ($injector) {
            return $injector;
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false)->shouldBeCalledTimes(1);

        $delegator = new GeneratedInjectorDelegator();
        $result = $delegator($container->reveal(), get_class($injector), $callback);

        $this->assertSame($result, $injector);
    }

    public function testGeneratedInjectorExists()
    {
        $injector = $this->prophesize(InjectorInterface::class)->reveal();
        $callback = function () use ($injector) {
            return $injector;
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')
            ->willReturn(true)
            ->shouldBeCalledTimes(1);
        $container->get('config')
            ->willReturn(['dependencies' => ['auto' => ['aot' => ['namespace' => 'ZendTest\Di\TestAsset']]]])
            ->shouldBeCalledTimes(1);

        $delegator = new GeneratedInjectorDelegator();
        $result = $delegator($container->reveal(), get_class($injector), $callback);

        $this->assertInstanceOf(TestAsset\GeneratedInjector::class, $result);
        $this->assertSame($injector, $result->getInjector());
    }
}
