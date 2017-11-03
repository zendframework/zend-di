<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di;

use Zend\Di\DefaultContainer;
use Zend\Di\InjectorInterface;

/**
 * @coversDefaultClass Zend\Di\DefaultContainer
 */
class DefaultContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|InjectorInterface
     */
    private function mockInjector()
    {
        return $this->getMockForAbstractClass(InjectorInterface::class);
    }

    /**
     * Tests DefaultContainer->setInstance()
     */
    public function testSetInstance()
    {
        $injector = $this->mockInjector();
        $injector->expects($this->never())->method($this->logicalNot($this->equalTo('')));
        $container = new DefaultContainer($injector);
        $expected = new \stdClass();
        $key = uniqid('Test');

        $container->setInstance($key, $expected);
        $this->assertTrue($container->has($key));
        $this->assertSame($expected, $container->get($key));
    }

    /**
     * Tests DefaultContainer->has()
     */
    public function testHasConsultatesInjector()
    {
        $injector = $this->mockInjector();
        $key = uniqid('TestClass');

        $injector->expects($this->atLeastOnce())
            ->method('canCreate')
            ->with($key)
            ->willReturn(true);

        $injector2 = $this->mockInjector();
        $injector2->expects($this->atLeastOnce())
            ->method('canCreate')
            ->with($key)
            ->willReturn(false);

        $container = new DefaultContainer($injector);
        $container2 = new DefaultContainer($injector2)
        ;
        $this->assertTrue($container->has($key));
        $this->assertFalse($container2->has($key));
    }

    /**
     * Tests DefaultContainer->get()
     */
    public function testGetUsesInjector()
    {
        $injector = $this->mockInjector();
        $key = uniqid('TestClass');
        $expected = new \stdClass();

        $injector->expects($this->atLeastOnce())
            ->method('create')
            ->with($key)
            ->willReturn($expected);

        $this->assertSame($expected, (new DefaultContainer($injector))->get($key));
    }

    /**
     * Tests DefaultContainer->get()
     */
    public function testGetInstanciatesOnlyOnce()
    {
        $injector = $this->mockInjector();
        $key = uniqid('TestClass');

        $injector->expects($this->once())
            ->method('create')
            ->with($key)
            ->willReturnCallback(function () {
                return new \stdClass();
            });

        $container = new DefaultContainer($injector);
        $expected = $container->get($key);
        $this->assertSame($expected, $container->get($key));
    }
}
